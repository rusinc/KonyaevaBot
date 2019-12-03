<?php


class CollegeClass {

    public const TYPE_NOT_CHANGED = 0;
    public const TYPE_CANCELLED = 1;
    public const TYPE_CHANGED = 2;
    public const TYPE_DEFAULT = -1;

    /**
     * @var string
     */
    private $name;
    /**
     * @var array|null
     */
    private $teachersAndRooms;
    /**
     * @var int
     */
    private $number;
    /**
     * @var int
     */
    private $type;

    /**
     * CollegeClass constructor.
     * @param int $number
     * @param int $type
     * @param string $name
     * @param array|null $teachersAndRooms
     * $teachersAndRooms имеет вид [Препод]=>"аудитория"
     */
    public function __construct(int $number,int $type,string $name = null, ?array $teachersAndRooms = null) {
        $this->name = $name;
        $this->teachersAndRooms = $teachersAndRooms;
        $this->number = $number;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getTeachersAndRooms(): ?array {
        return $this->teachersAndRooms;
    }

    /**
     * @return int
     */
    public function getNumber(): int {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

}