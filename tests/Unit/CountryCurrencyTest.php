<?php

namespace Tests\Unit;

use Tests\TestCase;

class CountryCurrencyTest extends TestCase
{
    public function test_usa_maps_to_usd(): void
    {
        foreach (['USA', 'US', 'United States'] as $name) {
            $currency = currency_for_country($name);

            $this->assertSame('USD', $currency['code']);
            $this->assertSame('$', $currency['symbol']);
            $this->assertSame('USD ($)', $currency['label']);
        }
    }

    public function test_uk_maps_to_gbp(): void
    {
        foreach (['UK', 'United Kingdom', 'GB'] as $name) {
            $currency = currency_for_country($name);

            $this->assertSame('GBP', $currency['code']);
            $this->assertSame('£', $currency['symbol']);
            $this->assertSame('GBP (£)', $currency['label']);
        }
    }

    public function test_unknown_country_defaults_to_gbp(): void
    {
        $currency = currency_for_country(null);

        $this->assertSame('GBP', $currency['code']);
        $this->assertSame('£', $currency['symbol']);
    }

    public function test_format_currency_amount_includes_symbol(): void
    {
        $this->assertSame('$ 1,086', format_currency_amount(1086, 'USA'));
        $this->assertSame('£ 1,086', format_currency_amount(1086, 'UK'));
    }
}
