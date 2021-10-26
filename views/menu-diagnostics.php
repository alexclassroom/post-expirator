<form method="post" id="postExpiratorMenuUpgrade">
    <?php
    wp_nonce_field('postexpirator_menu_diagnostics', '_postExpiratorMenuDiagnostics_nonce'); ?>
    <h3><?php
        _e('Advanced Diagnostics', 'post-expirator'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="postexpirator-log"><?php
                    _e('Debug Logging', 'post-expirator'); ?></label>
            </th>
            <td>
                <?php
                if (POSTEXPIRATOR_DEBUG) {
                    echo '
								<input type="submit" class="button" name="debugging-disable" id="debugging-disable" value="(' . __(
                            'Status: Enabled',
                            'post-expirator'
                        ) . ') ' . __('Disable Debugging', 'post-expirator') . '" />
								<br/><a href="' . admin_url(
                            'options-general.php?page=post-expirator.php&tab=viewdebug'
                        ) . '">' . __('View Debug Logs', 'post-expirator') . '</a>';
                } else {
                    echo '<input type="submit" class="button" name="debugging-enable" id="debugging-enable" value="(' . __(
                            'Status: Disabled',
                            'post-expirator'
                        ) . ') ' . __('Enable Debugging', 'post-expirator') . '" />';
                }
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php
                _e('Purge Debug Log', 'post-expirator'); ?></th>
            <td>
                <input type="submit" class="button" name="purge-debug" id="purge-debug"
                       value="<?php
                       _e('Purge Debug Log', 'post-expirator'); ?>"/>
            </td>
        </tr/>
        <tr valign="top">
            <th scope="row"><?php
                _e('WP-Cron Status', 'post-expirator'); ?></th>
            <td>
                <?php
                echo PostExpirator_CronFacade::is_cron_enabled() ?
                    _e('ENABLED - OK', 'post-expirator')
                    : _e('DISABLED', 'post-expirator');
                ?>
            </td>
        </tr/>
        <tr valign="top">
            <th scope="row"><label for="cron-schedule"><?php
                    _e('Current Cron Schedule', 'post-expirator'); ?></label>
            </th>
            <td>
                <?php
                $cron = PostExpirator_CronFacade::get_plugin_cron_events();

                if (empty($cron)) {
                    ''
                    ?>
                    <p><?php
                        _e('No cron events found for the plugin.', 'post-expirator'); ?></p>
                    <?php
                } else {
                    ?>
                    <p><?php
                        _e(
                            'The below table will show all currently scheduled cron events for the plugin with the next run time.',
                            'post-expirator'
                        ); ?></p>

                    <div class="pe-scroll">
                        <table cellspacing="0" class="striped">
                            <tr>
                                <th style="width: 30%"><?php
                                    _e('Date', 'post-expirator'); ?></th>
                                <th style="width: 30%;"><?php
                                    _e('Event', 'post-expirator'); ?></th>
                                <th style="width: 30%;"><?php
                                    _e('Arguments / Schedule', 'post-expirator'); ?></th>
                            </tr>
                            <?php
                            foreach ($cron as $time => $value) {
                                foreach ($value as $eventkey => $eventvalue) {
                                    echo '<tr class="pe-event">';
                                    echo '<td>' . date_i18n('r', $time) . '</td>';
                                    echo '<td>' . $eventkey . '</td>';
                                    $arrkey = array_keys($eventvalue);
                                    echo '<td>';
                                    foreach ($arrkey as $eventguid) {
                                        echo '<table><tr>';
                                        if (empty($eventvalue[$eventguid]['args'])) {
                                            echo '<td>' . __('No Arguments', 'post-expirator') . '</td>';
                                        } else {
                                            echo '<td>';
                                            $args = array();
                                            foreach ($eventvalue[$eventguid]['args'] as $key => $value) {
                                                $args[] = "$key => $value";
                                            }
                                            echo implode("<br/>\n", $args);
                                            echo '</td>';
                                        }
                                        if (empty($eventvalue[$eventguid]['schedule'])) {
                                            echo '<td>' . __('Single Event', 'post-expirator') . '</td>';
                                        } else {
                                            echo '<td>' . $eventvalue[$eventguid]['schedule'] . ' (' . $eventvalue[$eventguid]['interval'] . ')</td>';
                                        }
                                        echo '</tr></table>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </table>
                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
    </table>
</form>
<?php
