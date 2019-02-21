<?php

namespace Nomensa\FormBuilder\Helpers;

class DateStringHelper
{

    /**
     * Cleans up an accepted date string and returns in proper format or returns null if
     * Ensures the returned date is always YYYY-MM-DD
     *
     * Converts:
     *     "05 04 2018" -> 2018-04-05
     *     "05 4 2018"  -> 2018-04-05
     *     "5 04 2018"  -> 2018-04-05
     *     "2018 04 05" -> 2018-04-05
     *     "2018 4 5"   -> 2018-04-05
     *     "2018-4-5"   -> 2018-04-05
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function formatIfDateString($value): ?string
    {
        $value = preg_replace('/ {2,}/', ' ', trim($value));

        if (!preg_match('/^\d{1,4}(-| )\d{1,2}(-| )\d{1,4}$/', $value)) {
            return null;
        }

        $value = substr(trim($value), 0, 10);

        $value = str_replace(' ', '-', $value);

        $parts = explode('-', $value);

        if (isset($parts) and (count($parts) == 3)) {

            foreach ($parts as $index => $part) {

                // pack the string to 2 characters long if its less than that
                if (strlen($part) < 2) {
                    $parts[$index] = '0' . $part;
                }
            }

            // If required, reverse elements so they are in Year-Month-Day order
            if (isset($parts[0]) && (strlen($parts[0]) == 2)) {
                $value = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } else {
                $value = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
            }
        }

        return $value;
    }

}
