<?php

namespace app\models;

use app\models\behaviors\ChangelogBehavior;
use app\models\common\GeneToDisease;
use app\models\traits\ConditionActiveRecordTrait;
use app\models\traits\RuEnActiveRecordTrait;
use app\models\common\GeneToProteinClass;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property int[] $functionalClustersIdsArray
 * @property int[] $diseasesIdsArray
 * @property int[] $commentCauseIdsArray
 * @property int[] $proteinClassesIdsArray
 * @property array $functionalClustersArray
 */
class Gene extends common\Gene
{
    use ConditionActiveRecordTrait;

    public $newGenesNcbiIds;
    public $filledExperiments;

    protected $functionalClustersIdsArray;
    protected $diseasesIdsArray;
    protected $commentCauseIdsArray;
    protected $proteinClassesIdsArray;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
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
            [['functionalClustersIdsArray', 'diseasesIdsArray', 'commentCauseIdsArray',
                'proteinClassesIdsArray', 'newGenesNcbiIds', 'filledExperiments'], 'safe'],
            ['ncbi_id', 'unique'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'age_id' => 'Происхождение',
            'symbol' => 'HGNC',
            'aliases' => 'Синонимы',
            'name' => 'Название',
            'ncbi_id' => 'NCBI id',
            'uniprot' => 'Uniprot',
            'why' => 'why',
            'band' => 'Band',
            'locationStart' => 'Location Start',
            'locationEnd' => 'Location End',
            'orientation' => 'Orientation',
            'accPromoter' => 'Acc Promoter',
            'accOrf' => 'Acc Orf',
            'accCds' => 'Acc Cds',
            'references' => 'References',
            'orthologs' => 'Orthologs',
            'commentEvolution' => 'Эволюция',
            'commentFunction' => 'Описание белка Ru',
            'commentCause' => 'Причины отбора',
            'commentAging' => 'Связь со старением/долголетием',
            'commentEvolutionEN' => 'Эволюция En',
            'commentFunctionEN' => 'Описание белка En',
            'commentAgingEN' => 'Связь со старением/долголетием En',
            'commentsReferenceLinks' => 'Ссылки на источники',
            'functionalClusters' => 'Возрастозависимые процессы',
            'functionalClustersIdsArray' => 'Возрастозависимые процессы',
            'diseasesIdsArray' => 'Заболевания',
            'commentCauseIdsArray' => 'Причины отбора',
            'userEdited' => 'User Edited',
            'isHidden' => 'Скрыт',
            'proteinClassesIdsArray' => 'Классы белков',
            'expressionChange' => 'Изменение экспрессии',
            'protein_complex_ru' => 'Белковый комплекс Ru',
            'protein_complex_en' => 'Белковый комплекс En',
            'summary_ru' => 'Описание гена (NCBI) Ru',
            'summary_en' => 'Описание гена (NCBI) En',
            'source' => 'Источник',
        ];
    }

    public const EXPERIMENTS = [
        'lifespan_experiment' => 'LifespanExperiment',
        'age_related_change' => 'AgeRelatedChange',
        'gene_intervention_to_vital_process' => 'GeneInterventionToVitalProcess',
        'protein_to_gene' => 'ProteinToGene',
        'gene_to_progeria' => 'GeneToProgeria',
        'gene_to_longevity_effect' => 'GeneToLongevityEffect',
        'gene_to_additional_evidence' => 'GeneToAdditionalEvidence',
    ];

    public function search($params = [])
    {
        $query = self::find();

        if ($params) {
            $this->load($params);
        }
        $this->addCondition($query, 'id');
        $this->addCondition($query, 'symbol', true);
        $this->addCondition($query, 'aliases', true);
        $this->addCondition($query, 'name', true);
        $this->addCondition($query, 'ncbi_id');
        $this->addCondition($query, 'source');
        $this->addExperimentsCondition($query);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->prepare();
        return $dataProvider;
    }

    private function addExperimentsCondition(ActiveQuery &$query) // todo make by query builder
    {
        if ($this->filledExperiments) {
            $conditionType = '';
            $conditionString = '';
            foreach (self::EXPERIMENTS as $table => $experiment) {
                $query->leftJoin($table, $table . '.gene_id = gene.id');
                if ($this->filledExperiments === '+') {
                    $conditionString .= " {$conditionType} {$table}.gene_id is not null";
                    $conditionType = 'or';
                } else {
                    $conditionString .= " {$conditionType} {$table}.gene_id is null";
                    $conditionType = 'and';
                }
            }
            $query->groupBy('gene.id');
            $query->andWhere($conditionString);
        }
    }

    public function getFunctionalClustersIdsArray()
    {
        return FunctionalCluster::find()
            ->select('functional_cluster.id')
            ->join('INNER JOIN', 'gene_to_functional_cluster', 'gene_to_functional_cluster.functional_cluster_id = functional_cluster.id')
            ->where(['gene_to_functional_cluster.gene_id' => $this->id])
            ->asArray()
            ->column();
    }

    public function getDiseasesIdsArray()
    {
        return Disease::find()
            ->select('disease.id')
            ->join('INNER JOIN', 'gene_to_disease', 'gene_to_disease.disease_id = disease.id')
            ->where(['gene_to_disease.gene_id' => $this->id])
            ->asArray()
            ->column();
    }

    public function getCommentCauseIdsArray()
    {
        return CommentCause::find()
            ->select('comment_cause.id')
            ->join('INNER JOIN', 'gene_to_comment_cause', 'gene_to_comment_cause.comment_cause_id = comment_cause.id')
            ->where(['gene_to_comment_cause.gene_id' => $this->id])
            ->asArray()
            ->column();
    }

    public function getProteinClassesIdsArray()
    {
        return ProteinClass::find()
            ->select('protein_class.id')
            ->join('INNER JOIN', 'gene_to_protein_class', 'gene_to_protein_class.protein_class_id = protein_class.id')
            ->where(['gene_to_protein_class.gene_id' => $this->id])
            ->asArray()
            ->column();
    }

    public function setFunctionalClustersIdsArray(array $ids)
    {
        $this->functionalClustersIdsArray = $ids;
    }

    public function setDiseasesIdsArray(array $ids)
    {
        $this->diseasesIdsArray = $ids;
    }

    public function setCommentCauseIdsArray(array $ids)
    {
        $this->commentCauseIdsArray = $ids;
    }

    public function setProteinClassesIdsArray(array $ids)
    {
        $this->proteinClassesIdsArray = $ids;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (Yii::$app instanceof \yii\console\Application) { // todo продумать нормальный фикс
            return parent::afterSave($insert, $changedAttributes);
        }
        // todo move to relational active records
        $currentFunctionalClustersIds = $this->getFunctionalClustersIdsArray();
        if ($currentFunctionalClustersIds !== $this->functionalClustersIdsArray) {
            if ($this->functionalClustersIdsArray) {
                $functionalClustersIdsToDelete = array_diff($currentFunctionalClustersIds, $this->functionalClustersIdsArray);
                $functionalClustersIdsToAdd = array_diff($this->functionalClustersIdsArray, $currentFunctionalClustersIds);
                foreach ($functionalClustersIdsToAdd as $functionalClusterIdToAdd) {
                    $geneToFunctionalCluster = new GeneToFunctionalCluster();
                    $geneToFunctionalCluster->gene_id = $this->id;
                    $geneToFunctionalCluster->functional_cluster_id = $functionalClusterIdToAdd;
                    $geneToFunctionalCluster->save();
                }
            } else {
                $functionalClustersIdsToDelete = $currentFunctionalClustersIds;
            }
            $arsToDelete = GeneToFunctionalCluster::find()->where(
                ['and', ['gene_id' => $this->id],
                ['in', 'functional_cluster_id', $functionalClustersIdsToDelete]]
            )->all();
            foreach ($arsToDelete as $arToDelete) { // one by one for properly triggering "afterDelete" event
                $arToDelete->delete();
            }
        }

        $currentCommentCauseIds = $this->getCommentCauseIdsArray();
        if ($currentCommentCauseIds !== $this->commentCauseIdsArray) {
            if ($this->commentCauseIdsArray) {
                $commentCausesIdsToDelete = array_diff($currentCommentCauseIds, $this->commentCauseIdsArray);
                $commentCausesIdsToAdd = array_diff($this->commentCauseIdsArray, $currentCommentCauseIds);
                foreach ($commentCausesIdsToAdd as $commentCauseIdToAdd) {
                    $geneToCommentCause = new GeneToCommentCause();
                    $geneToCommentCause->gene_id = $this->id;
                    $geneToCommentCause->comment_cause_id = $commentCauseIdToAdd;
                    $geneToCommentCause->save();
                }
            } else {
                $commentCausesIdsToDelete = $currentCommentCauseIds;
            }
            $arsToDelete = GeneToCommentCause::find()->where(
                ['and', ['gene_id' => $this->id],
                    ['in', 'comment_cause_id', $commentCausesIdsToDelete]]
            )->all();
            foreach ($arsToDelete as $arToDelete) { // one by one for properly triggering "afterDelete" event
                $arToDelete->delete();
            }
        }

        $currentProteinClassesIdsArray = $this->getProteinClassesIdsArray();
        if ($currentProteinClassesIdsArray !== $this->proteinClassesIdsArray) {
            if ($this->proteinClassesIdsArray) {
                $proteinClassesIdsToDelete = array_diff($currentProteinClassesIdsArray, $this->proteinClassesIdsArray);
                $proteinClassesIdsToAdd = array_diff($this->proteinClassesIdsArray, $currentProteinClassesIdsArray);
                foreach ($proteinClassesIdsToAdd as $proteinClassesIdToAdd) {
                    $geneToProteinClass = new GeneToProteinClass();
                    $geneToProteinClass->gene_id = $this->id;
                    $geneToProteinClass->protein_class_id = $proteinClassesIdToAdd;
                    $geneToProteinClass->save();
                }
            } else {
                $proteinClassesIdsToDelete = $currentProteinClassesIdsArray;
            }
            $arsToDelete = GeneToProteinClass::find()->where(
                ['and', ['gene_id' => $this->id],
                    ['in', 'protein_class_id', $proteinClassesIdsToDelete]]
            )->all();
            foreach ($arsToDelete as $arToDelete) { // one by one for properly triggering "afterDelete" event
                $arToDelete->delete();
            }
        }

        $currentDiseasesIdsArray = $this->getDiseasesIdsArray();
        if ($currentDiseasesIdsArray !== $this->diseasesIdsArray) {
            if ($this->diseasesIdsArray) {
                $diseasesIdsArrayToDelete = array_diff($currentDiseasesIdsArray, $this->diseasesIdsArray);
                $diseasesIdsIdsToAdd = array_diff($this->diseasesIdsArray, $currentDiseasesIdsArray);
                foreach ($diseasesIdsIdsToAdd as $diseasesIdToAdd) {
                    $geneToDisease = new GeneToDisease();
                    $geneToDisease->gene_id = $this->id;
                    $geneToDisease->disease_id = $diseasesIdToAdd;
                    $geneToDisease->save();
                }
            } else {
                $diseasesIdsArrayToDelete = $currentDiseasesIdsArray;
            }
            $arsToDelete = GeneToDisease::find()->where(
                ['and', ['gene_id' => $this->id],
                    ['in', 'disease_id', $diseasesIdsArrayToDelete]]
            )->all();
            foreach ($arsToDelete as $arToDelete) { // one by one for properly triggering "afterDelete" event
                $arToDelete->delete();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function createByNCBIIds()
    {
        $genesNCBIIdsArray = explode(PHP_EOL, $this->newGenesNcbiIds);
        if (is_array($genesNCBIIdsArray)) {
            foreach ($genesNCBIIdsArray as $geneNCBIId) {
                $geneNCBIId = (int)trim($geneNCBIId, PHP_EOL . ' \t\n\r,;');
                $arGene = self::find()->where(['ncbi_id' => $geneNCBIId])->one();
                if (!$arGene) {
                    $arGene = new self();
                    $arGene->ncbi_id = $geneNCBIId;
                    $arGene->isHidden = 1;
                    if (!$arGene->save()) {
                        $this->addError('newGenesNcbiIds', current($arGene->getFirstErrors()));
                    }
                }
            }
        }
        return empty($this->errors);
    }

    public static function getAllNamesAsArray()
    {
        $result = parent::find()
            ->select(['id', 'concat(symbol, \' \', \'(\', name, \')\') as name'])
            ->all();
        return ArrayHelper::map($result, 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneToProteinActivities()
    {
        return $this->hasMany(GeneToProteinActivity::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLifespanExperiments()
    {
        return $this->hasMany(LifespanExperiment::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProteinToGenes()
    {
        return $this->hasMany(ProteinToGene::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgeRelatedChanges()
    {
        return $this->hasMany(AgeRelatedChange::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneToAdditionalEvidences()
    {
        return $this->hasMany(GeneToAdditionalEvidence::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneToLongevityEffects()
    {
        return $this->hasMany(GeneToLongevityEffect::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneInterventionToVitalProcesses()
    {
        return $this->hasMany(GeneInterventionToVitalProcess::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneToProgerias()
    {
        return $this->hasMany(GeneToProgeria::class, ['gene_id' => 'id']);
    }

    public function getAllExperimentsCounts()
    {
        $counts = [];
        foreach (self::EXPERIMENTS as $table => $experiment) {
            $count = $this->hasMany('app\models\\' . $experiment, ['gene_id' => 'id'])->count();
            if ($count) {
                $counts[$table] = $count;
            }
        }
        return $counts;
    }

}
