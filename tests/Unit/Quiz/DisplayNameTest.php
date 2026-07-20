<?php

declare(strict_types=1);

namespace Tests\Unit\Quiz;

use App\Services\Quiz\DisplayName;
use Tests\TestCase;

final class DisplayNameTest extends TestCase
{
    public function test_ordinary_names_are_accepted(): void
    {
        foreach (['Emmanuel', 'Grace O.', 'Tobi', 'Mary-Jane', "N'Golo"] as $name) {
            $this->assertTrue(DisplayName::isAcceptable($name), "{$name} should be allowed");
        }
    }

    public function test_profanity_is_rejected(): void
    {
        $this->assertFalse(DisplayName::isAcceptable('fuck'));
        $this->assertFalse(DisplayName::isAcceptable('BigDick'));
    }

    public function test_the_usual_evasions_are_caught(): void
    {
        foreach (['f u c k', 'sh1t', 'F.U.C.K', 'a$$', 'fu_ck'] as $name) {
            $this->assertFalse(DisplayName::isAcceptable($name), "{$name} should be blocked");
        }
    }

    public function test_names_that_impersonate_are_rejected(): void
    {
        $this->assertFalse(DisplayName::isAcceptable('Pastor'));
        $this->assertFalse(DisplayName::isAcceptable('admin'));
    }

    public function test_names_must_be_a_sensible_length(): void
    {
        $this->assertFalse(DisplayName::isAcceptable('a'));
        $this->assertFalse(DisplayName::isAcceptable(str_repeat('a', 25)));
        $this->assertFalse(DisplayName::isAcceptable('   '));
    }

    public function test_a_name_of_only_punctuation_is_rejected(): void
    {
        $this->assertFalse(DisplayName::isAcceptable('....'), 'Nothing legible would reach the projector');
    }

    public function test_cleaning_collapses_whitespace(): void
    {
        $this->assertSame('Grace O', DisplayName::clean('  Grace  O  '));
    }
}
