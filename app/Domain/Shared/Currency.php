<?php

namespace App\Domain\Shared;

/**
 * Currency formatting helpers previously living in app/Helpers/helpers.php.
 * Behaviour preserved verbatim.
 */
final class Currency
{
    public static function current(): array
    {
        $currency = ! empty(session()->get('selected_currency'))
            ? session()->get('selected_currency')
            : (setting('_general.currency') ?? 'USD');

        return ! empty($currency) ? currencyList($currency) : [];
    }

    public static function symbol(): string
    {
        $currencyDetail = self::current();
        $currencySymbol = '$';

        if (! empty($currencyDetail['symbol'])) {
            $currencySymbol = $currencyDetail['symbol'];
        }

        return $currencySymbol;
    }

    public static function format(mixed $amount, bool $currencySuperscript = false): string
    {
        $decimals = (int) (setting('_general.number_of_decimals') ?? 2);
        $decimalSeparator = (string) (! empty(setting('_general.decimal_separator')) ? setting('_general.decimal_separator') : '.');
        $thousandSeparator = (string) (! empty(setting('_general.thousand_separator')) ? setting('_general.thousand_separator') : ',');
        $formattedAmount = (string) number_format((float) $amount, $decimals, $decimalSeparator, $thousandSeparator);
        $currencyPosition = (string) (! empty(setting('_general.currency_position')) ? setting('_general.currency_position') : 'left');

        $symbol = self::symbol();

        return match ($currencyPosition) {
            'left' => $currencySuperscript ? '<sup>'.$symbol.'</sup>'.$formattedAmount : $symbol.$formattedAmount,
            'right' => $currencySuperscript ? $formattedAmount.'<sup>'.$symbol.'</sup>' : $formattedAmount.$symbol,
            'left_space' => $currencySuperscript ? '<sup>'.$symbol.'</sup>'.' '.$formattedAmount : $symbol.' '.$formattedAmount,
            'right_space' => $currencySuperscript ? $formattedAmount.' <sup>'.$symbol.'</sup>' : $formattedAmount.' '.$symbol,
            default => $formattedAmount,
        };
    }

    public static function formatWithoutDecimals(mixed $amount, bool $currencySuperscript = false): string
    {
        $thousandSeparator = (string) (! empty(setting('_general.thousand_separator')) ? setting('_general.thousand_separator') : ',');
        $formattedAmount = (string) number_format((float) $amount, 0, '', $thousandSeparator);
        $currencyPosition = (string) (! empty(setting('_general.currency_position')) ? setting('_general.currency_position') : 'left');

        $symbol = self::symbol();

        return match ($currencyPosition) {
            'left' => $currencySuperscript ? '<sup>'.$symbol.'</sup>'.$formattedAmount : $symbol.$formattedAmount,
            'right' => $currencySuperscript ? $formattedAmount.'<sup>'.$symbol.'</sup>' : $formattedAmount.$symbol,
            'left_space' => $currencySuperscript ? '<sup>'.$symbol.'</sup>'.' '.$formattedAmount : $symbol.' '.$formattedAmount,
            'right_space' => $currencySuperscript ? $formattedAmount.' <sup>'.$symbol.'</sup>' : $formattedAmount.' '.$symbol,
            default => $formattedAmount,
        };
    }

    public static function formatV2(mixed $amount): string
    {
        $decimals = (int) (! empty(setting('_general.number_of_decimals')) ? setting('_general.number_of_decimals') : 2);
        $decimalSeparator = (string) (! empty(setting('_general.decimal_separator')) ? setting('_general.decimal_separator') : '.');
        $thousandSeparator = (string) (! empty(setting('_general.thousand_separator')) ? setting('_general.thousand_separator') : ',');
        $formattedAmount = (string) number_format((float) $amount, $decimals, $decimalSeparator, $thousandSeparator);
        $currencyPosition = (string) (! empty(setting('_general.currency_position')) ? setting('_general.currency_position') : 'left');

        [$integerPart, $decimalPart] = explode($decimalSeparator, $formattedAmount);
        $symbol = self::symbol();

        return match ($currencyPosition) {
            'left' => '<sup>'.$symbol.'</sup>'.$integerPart.'.'.'<sub>'.$decimalPart.'</sub>',
            'right' => $integerPart.'.'.'<sub>'.$decimalPart.'</sub>'.'<sup>'.$symbol.'</sup>',
            'left_space' => '<sup>'.$symbol.'</sup>'.' '.$integerPart.'.'.'<sub>'.$decimalPart.'</sub>',
            'right_space' => $integerPart.'.'.'<sub>'.$decimalPart.'</sub>'.' '.'<sup>'.$symbol.'</sup>',
            default => $integerPart.'.'.'<sub>'.$decimalPart.'</sub>',
        };
    }
}
