<?php

namespace Tests\Unit;

use App\Support\HomepageArticle;
use PHPUnit\Framework\TestCase;

class HomepageArticleTest extends TestCase
{
    public function test_encoded_and_malformed_markup_cannot_keep_active_content(): void
    {
        $html = '<svg><a onload="alert(1)">SVG</a></svg><math><mtext>Math</mtext></math>'
            .'<p id="x" style="display:none" onclick="alert(1)">Text &amp; cafés '
            .'<a href="java&#x73;cript:alert(1)">bad</a><a href="jav&#x09;ascript:alert(1)">tab</a>'
            .'<a href="data:text/html,bad">data</a><img src="x" onerror="alert(1)"></p>'
            .'<a href="https://example.com/plans?a=1&amp;b=2" target="_blank">Plans</a>';
        $clean = HomepageArticle::clean($html);

        $this->assertSame('<p>Text &amp; cafés <a>bad</a><a>tab</a><a>data</a></p><a href="https://example.com/plans?a=1&amp;b=2">Plans</a>', $clean);
        $this->assertSame($clean, HomepageArticle::clean($clean));
    }

    public function test_plain_text_line_breaks_and_comparison_characters_are_preserved(): void
    {
        $clean = HomepageArticle::clean("First line\n5 < 10 & 10 > 5");
        $this->assertStringContainsString('First line<br>', $clean);
        $this->assertStringContainsString('5 &lt; 10 &amp; 10 &gt; 5', $clean);
        $this->assertSame('', HomepageArticle::clean('<p>&nbsp;<br></p>'));
    }
}
