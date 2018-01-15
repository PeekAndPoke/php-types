[![Code Coverage](https://scrutinizer-ci.com/g/PeekAndPoke/php-types/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/PeekAndPoke/psi/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PeekAndPoke/php-types/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PeekAndPoke/psi/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/PeekAndPoke/php-types/badges/build.png?b=master)](https://scrutinizer-ci.com/g/PeekAndPoke/psi/build-status/master)

# Why PHP-Types

There are some types that PHP does not provide as part of the language, e.g.:

- Enums

- LocalDate (a date object that enforces creation WITH timezone)


# TODO

( ) LocalDate::isSameDay

( ) LocalDate::isSameWeek

( ) LocalDate::isSameMonth

( ) LocalDate::isSameYear

( ) LocalDate::isSameDecade

( ) LocalDate::isSameCentury

( ) LocalDate::isSameMillennium

( ) extract all DST (daylight saving time aware methods from LocalDate)

$date->dst()->getShift()
$date->dst()->modifyByDays()
...
