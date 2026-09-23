<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class HomepageArticle
{
    public static function clean(?string $html): string
    {
        if (trim((string) $html) === '') {
            return '';
        }

        if (! preg_match('/<\/?[a-z][^>]*>/i', $html)) {
            $html = '<p>'.nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')).'</p>';
        }

        $source = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        try {
            $source->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $output = new DOMDocument('1.0', 'UTF-8');
        $root = $output->createElement('div');
        $output->appendChild($root);

        foreach ($source->getElementsByTagName('body')->item(0)?->childNodes ?? [] as $node) {
            self::copyNode($node, $root, $output);
        }

        // Empty editor paragraphs and non-breaking spaces should not show a blank card.
        if (preg_replace('/[\s\x{00a0}\x{200b}]+/u', '', $root->textContent) === '') {
            return '';
        }

        $clean = '';
        foreach ($root->childNodes as $node) {
            $clean .= $output->saveHTML($node);
        }

        return trim($clean);
    }

    private static function copyNode(DOMNode $node, DOMNode $parent, DOMDocument $output): void
    {
        if ($node instanceof DOMText) {
            $parent->appendChild($output->createTextNode($node->textContent));

            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'form', 'input', 'button', 'textarea', 'select', 'noscript'], true)) {
            return;
        }

        // The homepage already has an H1 and this section supplies its own H2.
        $tag = match ($tag) {
            'h1', 'h2' => 'h3',
            'b' => 'strong',
            'i' => 'em',
            default => $tag,
        };

        $target = $parent;
        if (in_array($tag, ['p', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'a', 'br', 'hr'], true)) {
            $target = $output->createElement($tag);
            $parent->appendChild($target);

            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                if (! preg_match('/[\x00-\x20\x7f\\\\]/', $href) && preg_match('~^(https?://|mailto:|tel:|/(?!/)|\#)~i', $href)) {
                    $target->setAttribute('href', $href);
                }

                if ($node->hasAttribute('title')) {
                    $target->setAttribute('title', $node->getAttribute('title'));
                }
            }
        }

        foreach ($node->childNodes as $child) {
            self::copyNode($child, $target, $output);
        }
    }
}
