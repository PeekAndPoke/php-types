<?php
/**
 * File was created 12.04.2016 06:54
 */
namespace PeekAndPoke\Types;

/**
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class LocalTimeslot
{
    /**
     * @var LocalDate
     *
     * @PeekAndPoke\Component\Slumber\Annotation\Slumber\AsLocalDate()
     */
    private $from;

    /**
     * @var LocalDate
     *
     * @PeekAndPoke\Component\Slumber\Annotation\Slumber\AsLocalDate()
     */
    private $to;

    /**
     * @return LocalTimeslot
     */
    public static function createEmpty()
    {
        return new static();
    }

    /**
     * @param LocalDate $from
     * @param LocalDate $to
     *
     * @return LocalTimeslot
     */
    public static function from(LocalDate $from, LocalDate $to)
    {
        $ret       = new static();
        $ret->from = $from;
        $ret->to   = $to;

        return $ret;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->isValid()
            ? $this->from->format() . ' - ' . $this->to->format()
            : '';
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->from !== null
               && $this->to !== null
               && $this->from->isBefore($this->to);
    }

    /**
     * @param LocalTimeslot $other
     *
     * @return bool
     */
    public function equals(LocalTimeslot $other)
    {
        return $this->isValid()
               && $other->isValid()
               && $this->from->isEqual($other->from)
               && $this->to->isEqual($other->to);
    }

    /**
     * @return LocalDate
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return LocalDate
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param LocalDate $from
     *
     * @return $this
     */
    public function setFrom(LocalDate $from = null)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param LocalDate $to
     *
     * @return $this
     */
    public function setTo(LocalDate $to = null)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Creates a new instance and moves the timeslot by the given number of days
     *
     * @param int $numDays
     *
     * @return LocalTimeslot
     */
    public function modifyByDays($numDays)
    {
        return self::from(
            $this->from->modifyByDays($numDays),
            $this->to->modifyByDays($numDays)
        );
    }

    /**
     * Creates a new instance and moves the timeslot by the given number of days
     * Takes into consideration the daylight savings possible issues
     *
     * @param int $numDays
     *
     * @return LocalTimeslot
     */
    public function modifyByDaysDaylightSavingAware($numDays)
    {
        return self::from(
            $this->from->modifyByDaysDaylightSavingAware($numDays),
            $this->to->modifyByDaysDaylightSavingAware($numDays)
        );
    }

    /**
     * @return int
     */
    public function getDurationInSecs()
    {
        if ($this->from && $this->to) {
            return $this->from->getTimestamp() - $this->to->getTimestamp();
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getDurationInMins()
    {
        return (int) ($this->getDurationInSecs() / 60);
    }
}
