<?php

namespace Drupal\server_json\Plugin;
use Drupal\block_content\BlockContentInterface;
use Drupal\Component\Serialization\Json;


class Slider1v1 extends WidgetMizaraBase
{
    function process(BlockContentInterface $block)
    {
        $bundle = $block->bundle();
        $formatter_json = $this->getJSONFormatter($bundle);
        $formatter_array = Json::decode($formatter_json);
        $items = $this->autoConvertToken($block,$formatter_array);
        return  $items[0] ;
    }
    public function func_paragraphs($entity,$field_name,$items){
        $result = [];
        if($entity->{$field_name}){

            $bundle = $entity->bundle();
            $ref = 'paragraph_type';
            $object_list = method_exists($entity->{$field_name}, 'referencedEntities') ? $entity->{$field_name}->referencedEntities() : [] ;
            if(!empty($object_list)){
                foreach(array_values($object_list) as $key =>  $object){
                    $position = $object->position->value ;
                    $formatter_json = $this->getJSONFormatter($position.'.'.$bundle);
                    $formatter_array = Json::decode($formatter_json);
                    $fields =['content' => 'text','media'=> 'media_image'];
                    $result[] = $this->convertTokenGlobalArray($object, $fields, $formatter_array);

                }
            }
        }
        $items[0]['elements'] = $result ;
        return $items;
    }
}