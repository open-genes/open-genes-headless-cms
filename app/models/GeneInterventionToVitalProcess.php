<?php

namespace app\models;

use app\models\behaviors\ChangelogBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "age".
 *
 */
class GeneInterventionToVitalProcess extends common\GeneInterventionToVitalProcess
{
    public $delete = false;

    public function behaviors()
    {
        return [
            ChangelogBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(), [
            [['gene_id', 'gene_intervention_id'], 'required'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(
            parent::attributeLabels(), [
            'delete' => 'Удалить'
        ]);
    }

    public function beforeValidate()
    {
        $this->age = str_replace(',', '.', $this->age);

        return parent::beforeValidate();
    }

    public static function findAllAsArray()
    {
        $result = [];
        $ages = self::find()->all();
        foreach ($ages as $age) {
            $result[$age->id] = $age->name_phylo;
        }

        return $result;
    }

    /**
     * @param array $modelArrays
     * @param int $geneId
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function saveMultipleForGene(array $modelArrays, int $geneId)
    {
        foreach ($modelArrays as $id => $modelArray) {
            if($modelArray['gene_intervention_id'] && $modelArray['model_organism_id']) {
                if(is_numeric($id)) {
                    $modelAR = self::findOne($id);
                } else {
                    $modelAR = new self();
                }
                if ($modelArray['delete'] === '1') {
                    $modelAR->delete();
                    continue;
                }
                $modelAR->setAttributes($modelArray);
                if(!is_numeric($modelArray['gene_intervention_id'])) {
                    $arProteinActivityObject = GeneIntervention::createFromNameString($modelArray['gene_intervention_id']);
                    $modelAR->gene_intervention_id = $arProteinActivityObject->id;
                }
                if(!is_numeric($modelArray['model_organism_id'])) {
                    $arProcessLocalization = ModelOrganism::createFromNameString($modelArray['model_organism_id']);
                    $modelAR->model_organism_id = $arProcessLocalization->id;
                }
                if(!is_numeric($modelArray['vital_process_id'])) {
                    $arVitalProcess = VitalProcess::createFromNameString($modelArray['vital_process_id']);
                    $modelAR->vital_process_id = $arVitalProcess->id;
                }
                if(!empty($modelArray['organism_line_id']) && !is_numeric($modelArray['organism_line_id'])) {
                    $arOrganismLine = OrganismLine::createFromNameString($modelArray['organism_line_id']);
                    $modelAR->organism_line_id = $arOrganismLine->id;
                }
                $modelAR->gene_id = $geneId;
                if($modelAR->organism_line_id === '') {
                    $modelAR->organism_line_id = null;
                }
                if($modelAR->genotype === '') {
                    $modelAR->genotype = null;
                }
                if(!$modelAR->save()) {
                    var_dump($modelAR->errors); die;
                }
            }
        }
    }

}