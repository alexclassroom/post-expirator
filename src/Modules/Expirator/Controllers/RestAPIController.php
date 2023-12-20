<?php
/**
 * Copyright (c) 2022. PublishPress, All rights reserved.
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Settings\Models\TaxonomiesModel;
use WP_REST_Request;

defined('ABSPATH') or die('Direct access not allowed.');

class RestAPIController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @param HookableInterface $hooksFacade
     * @param callable $expirablePostModelFactory
     */
    public function __construct(
        HookableInterface $hooksFacade,
        $expirablePostModelFactory
    ) {
        $this->hooks = $hooksFacade;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_REST_API_INIT,
            [$this, 'handleRestAPIInit']
        );
    }

    public function handleRestAPIInit()
    {
        $this->registerRestRoute();
        $this->registerRestField();
    }

    private function registerRestRoute()
    {
        $apiNamespace = 'publishpress-future/v1';

        register_rest_route( $apiNamespace, '/post-expiration/(?P<postId>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getFutureActionData'],
            'permission_callback' => function () {
                return current_user_can(CapabilitiesAbstract::EXPIRE_POST);
            },
            'args' => [
                'postId' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'sanitize_callback' => 'absint',
                    'required' => true,
                    'type' => 'integer',
                ],
            ]
        ]);

        register_rest_route($apiNamespace, '/post-expiration/(?P<postId>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'saveFutureActionData'],
            'permission_callback' => function () {
                return current_user_can(CapabilitiesAbstract::EXPIRE_POST);
            },
            'args' => [
                'postId' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'sanitize_callback' => 'absint',
                    'required' => true,
                    'type' => 'integer',
                ],
                'enabled' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_bool($param);
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                    'required' => false,
                    'type' => 'bool',
                ],
                'date' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'sanitize_callback' => 'absint',
                    'required' => true,
                    'type' => 'integer',
                ],
                'action' => [
                    'validate_callback' => function ($param, $request, $key) {
                        // Get available future action actions using the service EXPIRATION_ACTIONS_MODEL.
                        $container = Container::getInstance();
                        $expirationActionsModel = $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);
                        $expirationActions = array_keys($expirationActionsModel->getActions());

                        return in_array($param, $expirationActions) || $param === '';
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                    'required' => true,
                    'type' => 'string',
                ],
                'terms' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_array($param);
                    },
                    'sanitize_callback' => function ($param, $request, $key) {
                        return array_map('absint', $param);
                    },
                    'required' => true,
                    'type' => 'array',
                ],
            ]
        ]);

        register_rest_route( $apiNamespace, '/taxonomies/(?P<postType>[a-z\-_0-9A-Z]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getPostTypeTaxonomies'],
            'permission_callback' => function () {
                return current_user_can(CapabilitiesAbstract::EXPIRE_POST);
            },
            'args' => [
                'postType' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return sanitize_key($param);
                    },
                    'sanitize_callback' => 'sanitize_key',
                    'required' => true,
                    'type' => 'string',
                ],
            ]
        ]);

        register_rest_route($apiNamespace, '/terms/(?P<taxonomy>[a-z\-_0-9A-Z]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getTaxonomyTerms'],
            'permission_callback' => function () {
                return current_user_can(CapabilitiesAbstract::EXPIRE_POST);
            },
            'args' => [
                'taxonomy' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return sanitize_key($param);
                    },
                    'sanitize_callback' => 'sanitize_key',
                    'required' => true,
                    'type' => 'string',
                ],
            ]
        ]);
    }

    private function registerRestField()
    {
        $container = Container::getInstance();
        $settingsModelFactory = $container->get(ServicesAbstract::POST_TYPE_SETTINGS_MODEL_FACTORY);

        $settingsModel = $settingsModelFactory();
        $settings = $settingsModel->getPostTypesSettings();

        $activePostTypes = array_filter($settings, function ($postTypeSettings) {
            return $postTypeSettings['active'];
        });
        $activePostTypes = array_keys($activePostTypes);

        foreach ($activePostTypes as $postType) {
            register_rest_field(
                $postType,
                'publishpress_future_action',
                [
                    'get_callback' => function ($post) {
                        $postModelFactory = $this->expirablePostModelFactory;
                        $postModel = $postModelFactory($post['id']);

                        $date = $postModel->getExpirationDateString(false);
                        $action = $postModel->getExpirationType();
                        $terms = $postModel->getExpirationCategoryIDs();
                        $taxonomy = $postModel->getExpirationTaxonomy();

                        if (empty($date)) {
                            $defaultDataModelFactory = Container::getInstance()->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY);
                            $defaultDataModel = $defaultDataModelFactory->create($post['post_type']);

                            $defaultExpirationDate = $defaultDataModel->getActionDateParts();
                            $date = $defaultExpirationDate['iso'];

                            $action = $defaultDataModel->getDefaultActionForPostType($post['post_type']);
                            $terms = [];
                            $taxonomy = '';
                        }

                        return [
                            'enabled' => $postModel->isExpirationEnabled(),
                            'date' => $date,
                            'action' => $action,
                            'terms' => $terms,
                            'taxonomy' => $taxonomy,
                        ];
                    },
                    'update_callback' => function ($value, $post) {
                        if (isset($value['enabled']) && (bool)$value['enabled']) {
                            $opts = [
                                'expireType' => sanitize_text_field($value['action']),
                                'category' => array_map('sanitize_text_field', $value['terms']),
                                'categoryTaxonomy' => sanitize_text_field($value['taxonomy']),
                            ];

                            $taxonomiesModel = new TaxonomiesModel();
                            $opts['category'] = $taxonomiesModel->normalizeTermsCreatingIfNecessary(
                                $opts['categoryTaxonomy'],
                                $opts['category']
                            );

                            do_action(
                                HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
                                $post->ID,
                                strtotime($value['date']),
                                $opts
                            );
                            return true;
                        }

                        $this->hooks->doAction(HooksAbstract::ACTION_UNSCHEDULE_POST_EXPIRATION, $post->ID);

                        return true;
                    },
                    'schema' => [
                        'description' => 'Future action',
                        'type' => 'object',
                    ]
                ]
            );
        }
    }

    public function getPostTypeTaxonomies(WP_REST_Request $request)
    {
        $postType = $request->get_param('postType');
        $postType = sanitize_key($postType);

        $taxonomies = get_object_taxonomies($postType, 'objects');
        $taxonomies = array_map(function ($taxonomy) {
            return [
                'name' => $taxonomy->name,
                'label' => $taxonomy->label,
            ];
        }, $taxonomies);

        return rest_ensure_response(['taxonomies' => $taxonomies, 'count' => count($taxonomies)]);
    }

    public function getTaxonomyTerms(WP_REST_Request $request)
    {
        $taxonomy = $request->get_param('taxonomy');
        $taxonomy = sanitize_key($taxonomy);

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ]);

        $response = [];
        if (is_wp_error($terms)) {
            $response = ['terms' => [], 'count' => 0, 'taxonomyName' => ''];
        } else {
            $terms = array_map(function ($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }, $terms);

            $taxonomyName = get_taxonomy($taxonomy)->labels->name;
            $response = ['terms' => $terms, 'count' => count($terms), 'taxonomyName' => $taxonomyName];
        }

        return rest_ensure_response($response);
    }

    public function getFutureActionData(WP_REST_Request $request)
    {
        $postId = $request->get_param('postId');
        $container = Container::getInstance();
        $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
        $expirablePostModel = $factory($postId);

        $data = $expirablePostModel->getExpirationDataAsArray();

        // return the data as a JSON response
        return rest_ensure_response( $data );
    }

    public function saveFutureActionData(WP_REST_Request $request)
    {
        $postId = absint($request->get_param('postId'));

        $expirationEnabled = (bool)$request->get_param('enabled');

        if ($expirationEnabled) {
            $opts = [
                'expireType' => sanitize_key($request->get_param('action')),
                'category' => array_map('absint', (array)$request->get_param('terms')),
                'categoryTaxonomy' => sanitize_key($request->get_param('taxonomy'))
            ];

            do_action(HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION, $postId, absint($request->get_param('date')), $opts);
        } else {
            do_action(HooksAbstract::ACTION_UNSCHEDULE_POST_EXPIRATION, $postId);
        }

        return rest_ensure_response(true);
    }
}
