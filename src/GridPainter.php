<?php


use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class GridPainter {

    private const DEFAULT_TITLE_TEXT_SIZE = 50; //Размер шрифта для заголовка в пунктах
    private const DEFAULT_CLASS_TEXT_SIZE = 20; //Размер шрифта для предметов в пунктах

    //Позиции разделительных линий в рамках предметов (проценты от общей длины)
    private const CLASS_BOXES_PERCENTAGE = [10,55];

    private const TEXT_MARGIN = 10; //Расстояние текста от границ рамок и расстояние между строками в пикселях

    //режимы выравнивания текста
    private const TEXT_ALIGN_LEFT = 0;
    private const TEXT_ALIGN_CENTER = 1;
    private const TEXT_ALIGN_RIGHT = 2;

    private const FONT = "../resources/ConceptoTitulNrWv.ttf";

    /**
     * @var string
     */
    private $title;
    /**
     * @var Point
     */
    private $titlePos;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var string
     */
    private $savePath;
    /** @var ImageInterface  */
    private $image;
    /**
     * @var Box
     */
    private $titleBox;
    /**
     * @var Point
     */
    private $fieldPos;
    /**
     * @var Box
     */
    private $fieldBox;

    public function __construct(string $filename, string $savePath, string $title, Point $titlePos,Box $titleBox, Point $fieldPos, Box $fieldBox) {
        $this->title = $title;
        $this->filename = $filename;
        $this->savePath = $savePath;
        $this->titlePos = $titlePos;
        $this->titleBox = $titleBox;
        $this->fieldPos = $fieldPos;
        $this->fieldBox = $fieldBox;
        $imagine = new Imagine();
        $this->image = $imagine->open($filename);
    }

    /**
     * @param CollegeClass[] $classes
     */
    public function drawSchedule(array $classes){
        $this->drawTitle();

        $this->drawClasses($classes);

        $this->image->save($this->savePath);
    }

    private function drawTitle(){
        $this->putTextInBox(
            $this->titleBox,
            $this->titlePos,
            $this->title,
            self::DEFAULT_TITLE_TEXT_SIZE,
            self::TEXT_ALIGN_CENTER,
            new Color("000")
        );
    }

    /**
     * @param array|CollegeClass[] $classes
     */
    private function drawClasses(array $classes) {
        $total = count($classes);
        $height = floor($this->fieldBox->getHeight()/$total);

        $boxesColor = new Color([255,0,0]);
        $boxesThickness = 1;

        foreach ($classes as $class){
            $ox = $this->fieldPos->getX();
            if(!isset($y)){
                $y = $this->fieldPos->getY();
            }else{
                $y += $height;
            }
            //Рисуем общую рамку
            $this->drawBox(new Point($ox,$y),new Point($ox+$this->fieldBox->getWidth(),$y+$height),$boxesColor,$boxesThickness);

            $nx = $ox+floor($this->fieldBox->getWidth()/100*self::CLASS_BOXES_PERCENTAGE[0]);
            //Линия между первой и второй секциями
            $this->image->draw()->line(
                new Point($nx,$y),
                new Point($nx,$y+$height),
                $boxesColor,
                $boxesThickness
            );
            //Пишем номер пары
            $this->putTextInBox(
                new Box($nx-$ox,$height),
                new Point($ox,$y),
                $class->getNumber(),
                self::DEFAULT_CLASS_TEXT_SIZE,
                self::TEXT_ALIGN_LEFT,
                new Color("000")
            );

            $ox = $nx;
            $nx = $ox+floor($this->fieldBox->getWidth()/100*self::CLASS_BOXES_PERCENTAGE[1]);
            //Линия между второй и третььей секциями
            $this->image->draw()->line(
                new Point($nx,$y),
                new Point($nx,$y+$height),
                $boxesColor,
                $boxesThickness
            );
            //Пишем название пары
            $this->putTextInBox(
                new Box($nx-$ox,$height),
                new Point($ox,$y),
                $class->getType()==CollegeClass::TYPE_CANCELLED?"ОТМЕНА":$class->getName(),
                self::DEFAULT_CLASS_TEXT_SIZE,
                self::TEXT_ALIGN_LEFT,
                $class->getType()==CollegeClass::TYPE_CANCELLED?new Color([255,0,0]):new Color("000")
            );

            if($class->getTeachersAndRooms() === null)
                continue;

            $ox = $nx;
            $nx = $ox+floor($this->fieldBox->getWidth()/100*(100-array_sum(self::CLASS_BOXES_PERCENTAGE)));
            $subHeight = floor($height/count($class->getTeachersAndRooms()));
            foreach ($class->getTeachersAndRooms() as $teacher=>$room){
                if(!isset($subY)){
                    $subY = $y;
                }else{
                    $subY += $subHeight;
                }

                //Рисуем раздилительные линии между преподами
                $this->image->draw()->line(
                    new Point($ox,$subY+$subHeight),
                    new Point($nx,$subY+$subHeight),
                    $boxesColor,
                    $boxesThickness
                );
                //Пишем Препод -> аудитория
                $this->putTextInBox(
                    new Box($nx-$ox,$subHeight),
                    new Point($ox,$subY),
                    $teacher." -> ".$room,
                    self::DEFAULT_CLASS_TEXT_SIZE,
                    self::TEXT_ALIGN_LEFT,
                    new Color("000")
                );

            }
        }
    }

    private function drawBox(Point $pos1,Point $pos2,Color $color,int $thickness){
        if($pos1->getX()>$pos2->getX() || $pos1->getY()>$pos2->getY()){
            $minPos = new Point(min($pos1->getX(),$pos2->getX()),min($pos1->getY(),$pos2->getY()));
            $maxPos = new Point(max($pos1->getX(),$pos2->getX()),max($pos1->getY(),$pos2->getY()));
        }else{
            $minPos = $pos1;
            $maxPos = $pos2;
        }
        $this->image->draw()->line($minPos,new Point($minPos->getX(),$maxPos->getY()),$color,$thickness);
        $this->image->draw()->line(new Point($minPos->getX(),$maxPos->getY()),$maxPos,$color,$thickness);
        $this->image->draw()->line(new Point($maxPos->getX(),$minPos->getY()),$maxPos,$color,$thickness);
        $this->image->draw()->line($minPos,new Point($maxPos->getX(),$minPos->getY()),$color,$thickness);
    }

    private function putTextInBox(Box $box,Point $leftTopCorner,string $text,int $scale,int $align,Color $color){
        //Разбиваем текст на строки
        $lines = explode("\n",$text);

        $longestValue = strlen($lines[0]);
        //Подгоняем размер шрифта по ширине текста
        foreach ($lines as $k=>$line){
            $params = imageftbbox($scale,0,realpath(self::FONT),$line);
            if($box->getWidth()-self::TEXT_MARGIN*2-$params[2]-$params[0]<1){
                while ($box->getWidth()-self::TEXT_MARGIN*2-$params[2]-$params[0]<1){
                    $params = imageftbbox(--$scale,0,realpath(self::FONT),$line);
                }
            }
            if(strlen($line)>$longestValue){
                $longestValue = strlen($line);
            }
        }

        //Подгоняем размер шрифта по высоте
        $params = imageftbbox($scale,0,realpath(self::FONT),$lines[0]);
        if($box->getHeight()-self::TEXT_MARGIN*(count($lines)+1)-abs($params[1]-$params[7])*count($lines)<1){
            while ($box->getHeight()-self::TEXT_MARGIN*(count($lines)+1)-abs($params[1]-$params[7])*count($lines)<1){
                $params = imageftbbox(--$scale,0,realpath(self::FONT),$lines[0]);
            }
        }

        //печатаем текст
        foreach ($lines as $k=>$line){
            $params = imageftbbox($scale,0,realpath(self::FONT),$line);
            $strWidth = abs($params[2]-$params[0]);
            $strHeight = abs($params[1]-$params[7]);

            if(!isset($y)){
                $y = $leftTopCorner->getY()+self::TEXT_MARGIN;
            }else{
                $y += $strHeight+self::TEXT_MARGIN;
            }

            switch ($align){
                case self::TEXT_ALIGN_CENTER:
                    $x = ($leftTopCorner->getX()+$box->getWidth()/2)-$strWidth/2;
                    break;
                case self::TEXT_ALIGN_RIGHT:
                    $x = ($leftTopCorner->getX()+$box->getWidth())-$strWidth-self::TEXT_MARGIN;
                    break;
                case self::TEXT_ALIGN_LEFT:
                default:
                    $x = $leftTopCorner->getX()+self::TEXT_MARGIN;
                    break;
            }

            $startPoint = new Point((int)$x,(int)$y);

            $this->image->draw()->text(
                $line,
                new Font(realpath(self::FONT),$scale,$color),
                $startPoint
            );
        }
    }

}