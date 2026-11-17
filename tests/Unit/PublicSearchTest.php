<?php
/**
 * PublicSearch Unit Tests: search filters come from the parsed request (query vars), validated once.
 */

declare(strict_types=1);

namespace Readscope\Tests\Unit;

use Readscope\Support\PublicSearch;
use Readscope\Tests\TestCase;

class PublicSearchTest extends TestCase
{
    /** @var mixed */
    private $previousWp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousWp = $GLOBALS['wp'] ?? null;
        PublicSearch::reset();
        $_GET = [];
    }

    protected function tearDown(): void
    {
        PublicSearch::reset();
        $GLOBALS['wp'] = $this->previousWp;
        $_GET = [];
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $vars
     */
    private function request(array $vars): object
    {
        $wp = new \stdClass();
        $wp->query_vars = $vars;
        $GLOBALS['wp'] = $wp;
        PublicSearch::capture($wp);

        return $wp;
    }

    public function test_query_vars_are_registered_once(): void
    {
        $vars = PublicSearch::addQueryVars(['s', 'readscope_destination']);

        $this->assertSame(['s', 'readscope_destination', 'readscope_date', 'readscope_guests'], $vars);
    }

    public function test_values_are_read_from_the_parsed_request_and_validated(): void
    {
        $this->request(['readscope_date' => '2027-01-10', 'readscope_guests' => '4', 'readscope_destination' => 'Coxs Bazar']);

        $this->assertSame('2027-01-10', PublicSearch::date());
        $this->assertSame(4, PublicSearch::guests());
        $this->assertSame('coxs-bazar', PublicSearch::destination());
        $this->assertTrue(PublicSearch::hasDate());
        $this->assertTrue(PublicSearch::hasGuests());
    }

    public function test_date_and_guests_leave_the_main_query_destination_stays(): void
    {
        $wp = $this->request(['readscope_date' => '2027-01-10', 'readscope_guests' => '2', 'readscope_destination' => 'sylhet']);

        $this->assertSame(['readscope_destination' => 'sylhet'], $wp->query_vars);
        $this->assertSame('2027-01-10', PublicSearch::date());
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->request(['readscope_date' => '10/01/2027<script>', 'readscope_guests' => '999']);
        $this->assertSame('', PublicSearch::date());
        $this->assertTrue(PublicSearch::hasDate());
        $this->assertSame(50, PublicSearch::guests());

        PublicSearch::reset();
        $this->request(['readscope_date' => ['2027-01-10'], 'readscope_guests' => '-3']);
        $this->assertSame('2027-01-10', PublicSearch::date());
        $this->assertSame(3, PublicSearch::guests());

        PublicSearch::reset();
        $this->request(['readscope_guests' => '0']);
        $this->assertSame(1, PublicSearch::guests());
    }

    public function test_defaults_when_nothing_was_searched(): void
    {
        $this->request([]);

        $this->assertSame('', PublicSearch::date());
        $this->assertSame('', PublicSearch::destination());
        $this->assertSame(1, PublicSearch::guests());
        $this->assertSame(2, PublicSearch::guests(2));
        $this->assertFalse(PublicSearch::hasDate());
        $this->assertFalse(PublicSearch::hasGuests());
    }

    public function test_the_get_superglobal_is_never_consulted(): void
    {
        $_GET = ['readscope_date' => '2027-05-05', 'readscope_guests' => '9', 'readscope_destination' => 'dhaka'];
        $this->request([]);

        $this->assertSame('', PublicSearch::date());
        $this->assertSame(1, PublicSearch::guests());
        $this->assertSame('', PublicSearch::destination());
    }
}
