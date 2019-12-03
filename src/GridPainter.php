<?php


use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class GridPainter {

    private const DEFAULT_TEXT_SIZE = 20; //Размер шрифта в пунктах

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

        foreach ($classes as $class){
            $this->drawClass($class,count($classes));
        }

        $this->image->save($this->savePath);
    }

    private function drawTitle(){
        $this->putTextInBox($this->titleBox,$this->titlePos,$this->title,self::TEXT_ALIGN_CENTER);
    }

    private function drawClass(CollegeClass $class,int $totalClasses){
        //TODO рисовать сеточку для каждого предмета.
    }

    private function putTextInBox(Box $box,Point $leftTopCorner,string $text,int $align){
        //Разбиваем текст на строки
        $lines = explode("\n",$text);
        $scale = self::DEFAULT_TEXT_SIZE;

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
                new Font(realpath(self::FONT),$scale,new Color("000")),
                $startPoint
            );
        }
    }

}