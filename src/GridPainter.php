<?php


use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class GridPainter {

    /**
     * @var Point
     */
    private $pos1;
    /**
     * @var Point
     */
    private $pos2;
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

    public function __construct(string $filename,string $savePath, string $title,Point $titlePos, Point $fieldPos1, Point $fieldPos2) {
        $this->pos1 = new Point(min($fieldPos1->getX(),$fieldPos2->getX()),min($fieldPos1->getY(),$fieldPos2->getY()));
        $this->pos2 = new Point(max($fieldPos1->getX(),$fieldPos2->getX()),max($fieldPos1->getY(),$fieldPos2->getY()));
        $this->title = $title;
        $this->titlePos = $titlePos;
        $this->filename = $filename;
        $this->savePath = $savePath;
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
        $this->image->draw()->text(
            $this->title,
            new Font(realpath("../resources/ConceptoTitulNrWv.ttf"),40,new Color('000')),
            $this->titlePos
        );
    }

    private function drawClass(CollegeClass $class,int $totalClasses){
        //TODO рисовать сеточку для каждого предмета.
    }

}