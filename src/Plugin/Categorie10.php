<?php

namespace Drupal\server_json\Plugin;
use Drupal\block_content\BlockContentInterface;
use Drupal\Component\Serialization\Json;


class Categorie10 extends WidgetMizaraBase
{
    function process(BlockContentInterface $block)
    {
        $bundle = $block->bundle();
        $formatter_json = $this->getJSONFormatter($bundle);
        $formatter_array = Json::decode($formatter_json);
        $items = $this->autoConvertToken($block,$formatter_array);
        return  $items[0] ;
    }
    public function func_field_catalogue($entity,$field_name,$items){
        $result = [] ;
        $terms = $entity->get($field_name)->getValue();
        foreach ($terms as $key => $value) {
            $result[] = $value['target_id'];
        }
        $str = implode(",",$result);
        return $this->replaceValueInArray('%field_catalogue%',$str,$items);
    }


}