<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Normalizer;

/**
 * Catalog keyword search that understands Vietnamese diacritics.
 *
 * The products table uses an accent-insensitive collation, so a plain LIKE
 * treats "dây" as "day" and hits "Everyday". Matching is done here instead:
 * - a word typed with diacritics only matches the same accented word ("dây" ≠ "day", "dày");
 * - a word typed without them matches any accent ("day" → "dây", "dem" → "Đệm");
 * - words match from the start of a word, so "day" no longer hits "Everyday";
 * - only the product name is searched, and every word must appear in it.
 */
class ProductSearch
{
    /**
     * Ids of the products in $products that match $search, best match first.
     *
     * @return list<int>
     */
    public function rankedIds(Builder $products, string $search): array
    {
        $terms = $this->words($search);
        if ($terms === []) {
            return [];
        }

        $ranked = [];
        $products->select(['id', 'name'])->get()->each(function ($product) use ($terms, &$ranked) {
            $score = $this->score($terms, $this->words($product->name));
            if ($score > 0) {
                $ranked[(int) $product->id] = $score;
            }
        });
        arsort($ranked);

        return array_keys($ranked);
    }

    /**
     * @param  list<string>  $terms
     * @param  list<string>  $nameWords
     */
    private function score(array $terms, array $nameWords): int
    {
        $score = 0;
        foreach ($terms as $term) {
            $termScore = $this->wordScore($term, $nameWords);
            if ($termScore === 0) {
                return 0;
            }
            $score += $termScore;
        }

        if ($this->startsWith($nameWords, $terms)) {
            $score += 3;
        }

        return $score;
    }

    /**
     * 3 for the same word, 2 for the same word ignoring accents, 1 for a word prefix, 0 for no match.
     *
     * @param  list<string>  $words
     */
    private function wordScore(string $term, array $words): int
    {
        $best = 0;
        foreach ($words as $word) {
            $best = max($best, $this->matchTerm($term, $word));
            if ($best === 3) {
                break;
            }
        }

        return $best;
    }

    private function matchTerm(string $term, string $word): int
    {
        if ($word === $term) {
            return 3;
        }

        $accentSensitive = $this->fold($term) !== $term;
        $candidate = $accentSensitive ? $word : $this->fold($word);
        if ($candidate === $term) {
            return 2;
        }

        return str_starts_with($candidate, $term) ? 1 : 0;
    }

    /**
     * @param  list<string>  $nameWords
     * @param  list<string>  $terms
     */
    private function startsWith(array $nameWords, array $terms): bool
    {
        foreach ($terms as $index => $term) {
            if (! isset($nameWords[$index]) || $this->matchTerm($term, $nameWords[$index]) < 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function words(string $text): array
    {
        if (class_exists(Normalizer::class)) {
            $text = Normalizer::normalize($text, Normalizer::FORM_C) ?: $text;
        }

        return preg_split('/[^\p{L}\p{M}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    private function fold(string $word): string
    {
        return Str::lower(Str::ascii($word));
    }
}
