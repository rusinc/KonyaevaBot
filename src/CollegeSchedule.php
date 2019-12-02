<?php


class CollegeSchedule {

    public const MONDAY = 0;
    public const TUESDAY = 1;
    public const WEDNESDAY = 2;
    public const THURSDAY = 3;
    public const FRIDAY = 4;
    public const SATURDAY = 5;
    public const SUNDAY = 6;

    /**
     * @return array
     */
    public static function getAllClasses() : array {
        $classes = [];
        //Понедельник
        $classes[self::MONDAY] = [
            new CollegeClass(
                1,
                CollegeClass::TYPE_DEFAULT,
                "Практика в 10:00",
                [
                    "Accenture"=>"Зал для конференций \"Москва\""
                ]
            )
        ];
        //Вторник
        $classes[self::TUESDAY] = $classes[self::MONDAY];
        //Среда
        $classes[self::WEDNESDAY] = [
            new CollegeClass(
                1,
                CollegeClass::TYPE_DEFAULT,
                "Технология разработки и защиты баз данных",
                [
                    "Орлов Ю.П."=>306
                ]
            ),
            new CollegeClass(
                2,
                CollegeClass::TYPE_DEFAULT,
                "Численные методы",
                [
                    "Тулинова Ю.Л."=>21
                ]
            ),
            new CollegeClass(
                3,
                CollegeClass::TYPE_DEFAULT,
                "Численные методы",
                [
                    "Тулинова Ю.Л."=>21
                ]
            )
        ];
        //Четверг
        $classes[self::THURSDAY] = [
            new CollegeClass(
                4,
                CollegeClass::TYPE_DEFAULT,
                "ИСРПО",
                [
                    "Ишкова Л.Г."=>305
                ]
            ),
            new CollegeClass(
                5,
                CollegeClass::TYPE_DEFAULT,
                "Прикладное программирование",
                [
                    "Ишкова Л.Г."=>305
                ]
            ),
            new CollegeClass(
                6,
                CollegeClass::TYPE_DEFAULT,
                "WEB-программирование",
                [
                    "Ишкова Л.Г."=>305
                ]
            )
        ];
        //Пятница
        $classes[self::FRIDAY] = [
            new CollegeClass(
                1,
                CollegeClass::TYPE_DEFAULT,
                "Физ-ра",
                [
                    "Видьманов А.А."=>"с/з"
                ]
            ),
            new CollegeClass(
                2,
                CollegeClass::TYPE_DEFAULT,
                "Основы программирования",
                [
                    "Ишкова Л.Г."=>305
                ]
            ),
            new CollegeClass(
                3,
                CollegeClass::TYPE_DEFAULT,
                "Иностранный язык",
                [
                    "Мухина Е.Ю."=>8
                ]
            ),
            new CollegeClass(
                4,
                CollegeClass::TYPE_DEFAULT,
                "БЖД",
                [
                    "Шарапов А.В."=>13
                ]
            ),
        ];
        //Суббота
        $classes[self::SATURDAY] = [];
        //Воскресенье
        $classes[self::SUNDAY] = [];

        return $classes;
    }

    /**
     * @param int $day
     * @return CollegeClass[]
     */
    public static function getDay(int $day) : array {
        return self::getAllClasses()[$day];
    }

}