/**
 * External dependencies
 */
import deburr from 'lodash/deburr';
import differenceWith from 'lodash/differenceWith';
import find from 'lodash/find';
import words from 'lodash/words';


// Default search helpers
const defaultGetName = (item) => item.name || '';
const defaultGetTitle = (item) => item.label;
const defaultGetDescription = (item) => item.description || '';
const defaultGetKeywords = (item) => item.keywords || [];
const defaultGetCategory = (item) => item.category;

/**
 * Sanitizes the search input string.
 *
 * @param {string} input The search input to normalize.
 *
 * @return {string} The normalized search input.
 */
function normalizeSearchInput(input = '') {
	// Disregard diacritics.
	//  Input: "média"
	input = deburr(input);

	// Accommodate leading slash, matching autocomplete expectations.
	//  Input: "/media"
	input = input.replace(/^\//, '');

	// Lowercase.
	//  Input: "MEDIA"
	input = input.toLowerCase();

	return input;
}

/**
 * Converts the search term into a list of normalized terms.
 *
 * @param {string} input The search term to normalize.
 *
 * @return {string[]} The normalized list of search terms.
 */
export const getNormalizedSearchTerms = (input = '') => {
	// Extract words.
	return words(normalizeSearchInput(input));
};

const removeMatchingTerms = (unmatchedTerms, unprocessedTerms) => {
	return differenceWith(
		unmatchedTerms,
		getNormalizedSearchTerms(unprocessedTerms),
		(unmatchedTerm, unprocessedTerm) =>
			unprocessedTerm.includes(unmatchedTerm)
	);
};

export const searchNodeItems = (
	items,
	categories,
	searchInput
) => {
	const normalizedSearchTerms = getNormalizedSearchTerms(searchInput);
	if (normalizedSearchTerms.length === 0) {
		return items;
	}

	const config = {
		getCategory: (item) =>
			find(categories, { name: item.category })?.label,
		getName: (item) => item.name,
		getTitle: (item) => item.label,
		getDescription: (item) => item.description,
		getKeywords: (item) => item.keywords || [],
	};

	return searchItems(items, searchInput, config);
};

/**
 * Filters an item list given a search term.
 *
 * @param {Array}  items       Item list
 * @param {string} searchInput Search input.
 * @param {Object} config      Search Config.
 *
 * @return {Array} Filtered item list.
 */
export const searchItems = (items = [], searchInput = '', config = {}) => {
	const normalizedSearchTerms = getNormalizedSearchTerms(searchInput);
	if (normalizedSearchTerms.length === 0) {
		return items;
	}

	const rankedItems = items
		.map((item) => {
			return [item, getItemSearchRank(item, searchInput, config)];
		})
		.filter(([, rank]) => rank > 0);

	rankedItems.sort(([, rank1], [, rank2]) => rank2 - rank1);
	return rankedItems.map(([item]) => item);
};

/**
 * Get the search rank for a given item and a specific search term.
 * The better the match, the higher the rank.
 * If the rank equals 0, it should be excluded from the results.
 *
 * @param {Object} item       Item to filter.
 * @param {string} searchTerm Search term.
 * @param {Object} config     Search Config.
 *
 * @return {number} Search Rank.
 */
export function getItemSearchRank(item, searchTerm, config = {}) {
	const {
		getName = defaultGetName,
		getTitle = defaultGetTitle,
		getDescription = defaultGetDescription,
		getKeywords = defaultGetKeywords,
		getCategory = defaultGetCategory,
	} = config;

	const name = getName(item);
	const title = getTitle(item);
	const description = getDescription(item);
	const keywords = getKeywords(item);
	const category = getCategory(item);

	const normalizedSearchInput = normalizeSearchInput(searchTerm);
	const normalizedTitle = normalizeSearchInput(title);

	let rank = 0;

	// Prefers exact matches
	// Then prefers if the beginning of the title matches the search term
	// name, keywords, categories, variations match come later.
	if (normalizedSearchInput === normalizedTitle) {
		rank += 30;
	} else if (normalizedTitle.startsWith(normalizedSearchInput)) {
		rank += 20;
	} else {
		const terms = [
			name,
			title,
			description,
			...keywords,
			category,
		].join(' ');
		const normalizedSearchTerms = words(normalizedSearchInput);
		const unmatchedTerms = removeMatchingTerms(
			normalizedSearchTerms,
			terms
		);

		if (unmatchedTerms.length === 0) {
			rank += 10;
		}
	}

	// Give a better rank to "core" namespaced items.
	if (rank !== 0 && name.startsWith('core/')) {
		rank++;
	}

	return rank;
}
