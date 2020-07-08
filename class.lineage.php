<?php
class Lineage
{
    private $db;
    private $lab;

    public function __construct()
    {
        $this->db = DB::getInstance();
        $this->lab = new Lab();
    }

    public function showLineageNew($id){
     $build = json_decode($this->build($id));

     return "<div>
                <h3>{$build->name}'s Pedigree</h3>
                <div style='display: inline-block'>
                    <div class='tree'>
                        <ul>
                          ".$this->PedigreeTile($build,1)."
                        </ul>
                    </div>
                </div>
            </div>";
    }


    private function buildParents($father,$mother,$gen){
      $father = json_decode($father);
      $mother = json_decode($mother);

      if($father->ref != NULL && $mother->ref != NULL) {
        return "<ul class='pt-parents'>
                    ".$this->PedigreeTile($mother, $gen+1)."
                    ".$this->PedigreeTile($father, $gen+1)."
                </ul>";
      }else{
        return "";
      }
    }

    private function PedigreeTile($data, $gen){
        return "<li class='pedigree-tile'>
                <a class='pt-content' href='/view/{$data->ref}'>
                    <div class='pt-imgframe g{$gen}'>
                        <img
                            class='pixel g{$gen}'
                            src='/{$this->lab->renderImage($data->ref)}'
                        />
                    </div>
                    <br/>
                    <label class='pt-label'>{$data->name}</label>
                </a>
                ".$this->buildParents($data->father, $data->mother,$gen)."
            </li>";

    }

    private function build($id,$gen = 0){
      $gen++;

      $sql = "SELECT code, name, mother, father
              FROM cats_owned_cats
              WHERE code = ?";

      $creatureData = $this->db->query($sql, array($id))->first();

      if(empty($creatureData->name)){
        $name = $creatureData->code;
      }else{
        $name = $creatureData->name;
      }

      $return = array("gen"=>$gen,"name"=>$name,"ref"=>$id);

      if($gen < 6){
        if($creatureData->father != '0'){
          $return['father'] = $this->build($creatureData->father,$gen);
        }

        if($creatureData->mother != '0'){
          $return['mother'] = $this->build($creatureData->mother,$gen);
        }
      }
      return json_encode($return);
  }
}
