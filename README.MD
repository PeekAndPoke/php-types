# Why PHP-Types

There are some types that PHP does not provide as part of the language, e.g.:

- Enums

- LocalDate (a date object that enforces creation WITH timezone)


# TODO

( ) extract all DST (daylight saving time aware methods from LocalDate)

$date->dst()->getShift()
$date->dst()->modifyByDays()
...
