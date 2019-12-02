<?php

use Imagine\Image\Point;

require_once __DIR__."/autoload.php";

$group = "3-ПР1";

//Строка для дебага в средах без antiword
//$content = "7 ноября (четверг) Занятия, проходившие в 101, переносятся в 407 аудиторию. Занятия, проходившие в 302, переносятся в 307 (1), 408 (2, 3, 4) аудитории. Занятия, проходившие в 103, переносятся в 201 аудиторию. | |ПАРЫ |ГРУППЫ |1 |2 |3 |4 |5 |6 |  |2-ИБ |ИН.ЯЗ (ПОДГРУППА) Кудрявцева Л.И. 210 ауд. |РЯ и КР Смирнова К.С. чз | | |ОТМЕНА | |  |2-СА  |  | | | |ОТМЕНА | | |2-ТМС |Э и Э Баранов В.В. 406 ауд. | |ИН.ЯЗ (ВСЯ ГРУППА) Павлов А.В. 16 ауд. | | | | |1-ТОС | |ПДО Шарапов А.В. 13 ауд. |ПДО Шарапов А.В. 13 ауд. | | | | |2-ТО |ИН.ЯЗ (ВСЯ ГРУППА) Кудрявцева Л.И. 210 ауд. |ИН.ЯЗ (ВСЯ ГРУППА) Кудрявцева Л.И. 210 ауд, 406 ауд. | |ПДО Шарапов В.А. 13 ауд. | | | |2-Э1 | |ОБУ Барбашева С.Ю. 11 ауд. | | | | | |2-Э2 |ОТМЕНА | |ОБУ Барбашева С.Ю. 11 ауд. |ЭО Ширшикова В.Н. 407 ауд. | | | |2-БД | | | |БУ Барбашева С.Ю. 11 ауд. | | | |3-БД | | | |АФХД Попова М.А. 20 ауд. |АФХД Попова М.А. 20 ауд. | | |3-СП |КГ Романов Ю.М., Шарапова Л.В. 20, 308 ауд. | | | | | | |3-ПР1 | |ИН. ЯЗ Мухина Е.Ю. 8 ауд. |ИН. ЯЗ Мухина Е.Ю. 8 ауд. | |ОТМЕНА |ОТМЕНА | |4-ПР1 | |03.03 Петрушенко Л.Л. 310 ауд. |03.03 Петрушенко Л.Л. 310 ауд. |ОЭ Сумкина И.В 406 ауд. | | | |4-ПР2 | | |Физ-ра Видьманов А.А. сз |03.03 Петрушенко Л.Л. 310 ауд. |ОЭ Сумкина И.В. 201 ауд. |03.03 Петрушенко Л.Л. 310 ауд. | |4-ТО2 | | |Физ-ра Крупенина О.С. сз | | | | |";

/** @var CollegeClass[] $changes */
$changes = [];

//Скачиваем файл с сайта TODO улучшить. Я чую, что у них там еще и название файла не совсем статично
//file_put_contents("../out/ismras.doc",file_get_contents("http://www.tgiek.ru/files/timetables/ismras_0.doc"));


//Закомментировать, если в среде нет antiword (в докере он есть)
$content = shell_exec('antiword -w 0 -m UTF-8.txt '."../resources/ismras.doc"); //../resources/ismras.doc - это тестовый ресурс

$content = trim(str_replace("\n"," ",$content));

//Определяем число, месяц и день недели
$contentA = explode(" ",$content);

$day = $contentA[0];
$month = $contentA[1];
$weekDay = mb_substr($contentA[2],1,-1);

switch (mb_strtolower($weekDay)){
    case "понедельник":
        $weekDay = CollegeSchedule::MONDAY;
        break;
    case "вторник":
        $weekDay = CollegeSchedule::TUESDAY;
        break;
    case "среда":
        $weekDay = CollegeSchedule::WEDNESDAY;
        break;
    case "четверг":
        $weekDay = CollegeSchedule::THURSDAY;
        break;
    case "пятница":
        $weekDay = CollegeSchedule::FRIDAY;
        break;
    case "суббота":
        $weekDay = CollegeSchedule::SATURDAY;
        break;
    case "воскресенье":
        $weekDay = CollegeSchedule::SUNDAY;
        break;
}

unset($contentA);

//определяем число пар в таблице
$pos = strpos($content,"ГРУППЫ")+strlen("ГРУППЫ");

$last = "";
$cols = 0;

for($pos; $pos<strlen($content); $pos++){
    if($content[$pos]==" ")
        continue;
    $cur = $content[$pos];
    if($last == "|" && $cur == "|"){
        break;
    }
    if($cur!="|"){
        $cols++;
    }
    $last = $cur;
}

//Находим строку в таблице с нашей группой
$pos = strpos($content,$group,$pos+1);

if(!$pos){
    //TODO адаптировать под бота
    exit("Изменения для группы $group не найдены!");
}

for($i = 1;$i<=$cols;$i++){
    //Начало ячейки
    $begin = strpos($content,"|",$pos);
    //Конец ячейки
    $end = strpos($content,"|",$begin+1);


    //Контент ячейки
    $class = trim(substr($content,$begin+1,$end-$begin-1));

    $pairs = []; //Будущие комбинации преподов и аудиторий
    if($class=="ОТМЕНА"){
        $mode = CollegeClass::TYPE_CANCELLED;
    }elseif (empty($class)){
        $mode = CollegeClass::TYPE_NOT_CHANGED;
    }else{
        $mode = CollegeClass::TYPE_CHANGED;

        //Ищем всех преподов
        preg_match_all("/([А-Я]\w+\s[А-Я]\.[А-Я]\.?)/u",$class,$teachersM);

        $teachers = $teachersM[0]; //Преподы

        if(!empty($teachers)){
            $teachersStart = strpos($class,$teachers[0]);
            $lastTeacher = $teachers[count($teachers)-1];
            $teachersEnd = strpos($class,$lastTeacher)+strlen($lastTeacher);

            //Вся строка с аудиториями
            $roomsStr = trim(substr($class,strpos($class,$lastTeacher)+strlen($lastTeacher)));
            $rooms = explode(",",$roomsStr); //Аудитории
            $class = str_replace($roomsStr,"",$class); //Вычищаем аудитории из общей строки
            //Заменяем полные строки аудиторий их короткими кодами (без слова "ауд")
            foreach ($rooms as &$roomCode){
                $roomCode = explode(" ",trim($roomCode))[0];
            }

            //Вся строка с преподами
            $teachersStr = trim(substr($class,$teachersStart,$teachersEnd-$teachersStart));
            $class = str_replace($teachersStr,"",$class); //Убираем преподов из общей строки
            $class = trim($class);
            //Создаем ассоциации преподов с аудиториями
            $pairs = array_combine($teachers,$rooms);
        }
    }

    switch ($mode){
        case CollegeClass::TYPE_CANCELLED:
        case CollegeClass::TYPE_NOT_CHANGED:
            $changes[] = new CollegeClass($i,$mode);
            break;
        case CollegeClass::TYPE_CHANGED:
            $changes[] = new CollegeClass($i,$mode,$class,$pairs);
    }
    $pos = $end;
}

$classes = CollegeSchedule::getDay($weekDay);

//Заменяем штатные пары, если есть замены или отмены
array_walk(
    $changes,
    /** @var CollegeClass $class */
    function ($class,$key) use (&$classes){
        if($class->getType()!=CollegeClass::TYPE_NOT_CHANGED){
            $put = false;
            foreach ($classes as &$cl){
                if($cl->getNumber()==$class->getNumber()){
                    $cl = $class;
                    $put = true;
                    break;
                }
            }
            if(!$put){
                $classes[] = $class;
            }
        }
    }
);

//Сортируем предметы по номеру пары (они могут быть у нас неотсортированы к этому моменту)
usort(
    $classes,
    function ($a,$b){
        /** @var CollegeClass $a */
        /** @var  CollegeClass $b */
        if($a->getNumber()>$b->getNumber()){
            return 1;
        }else if($a->getNumber()<$b->getNumber()){
            return -1;
        }else{
            return 0;
        }
    }
);

//Печатаем для дебага пока что.
foreach ($classes as $class){
    if($class->getType()==CollegeClass::TYPE_CANCELLED){
        echo "ОТМЕНА ".$class->getNumber()." <br><br>";
        continue;
    }
    echo ($class->getType()!=CollegeClass::TYPE_DEFAULT?"ЗАМЕНА ":" ").$class->getNumber().") ".$class->getName()."<br>";
    foreach ($class->getTeachersAndRooms() as $teacher=>$room){
        echo $teacher." -> ".$room."<br>";
    }
    echo "<br>";
}

//Генерируем картинку для отправки в беседу
$gridPainter = new GridPainter("../resources/поиск.png","../out/schedule.png","test",new Point(0,0),new Point(0,0),new Point(0,0));
$gridPainter->drawSchedule($classes);