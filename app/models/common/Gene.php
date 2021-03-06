<?php

namespace app\models\common;

use Yii;

use function str_replace;

/**
 * This is the model class for table "gene".
 *
 * @property int $id
 * @property string $symbol
 * @property string $aliases
 * @property string $name
 * @property int $ncbi_id
 * @property string $uniprot
 * @property string $why
 * @property string $band
 * @property int $locationStart
 * @property int $locationEnd
 * @property int $orientation
 * @property string $accPromoter
 * @property string $accOrf
 * @property string $accCds
 * @property string $references
 * @property string $orthologs
 * @property string $commentEvolution
 * @property string $commentFunction
 * @property string $commentCause
 * @property string $commentAging
 * @property string $commentEvolutionEN
 * @property string $commentFunctionEN
 * @property string $commentAgingEN
 * @property string $commentsReferenceLinks
 * @property int $rating
 * @property int $isHidden
 * @property int $expressionChange
 * @property int $created_at
 * @property int $updated_at
 * @property int $age_id
 * @property string $protein_complex_ru
 * @property string $protein_complex_en
 * @property string $summary_ru
 * @property string $summary_en
 * @property string $ensembl
 * @property string $human_protein_atlas
 * @property string $source
 *
 * @property Phylum $age
 * @property AgeRelatedChange[] $ageRelatedChanges
 * @property GeneExpressionInSample[] $geneExpressionInSamples
 * @property GeneInterventionToVitalProcess[] $geneInterventionToVitalProcesses
 * @property GeneToCommentCause[] $geneToCommentCauses
 * @property GeneToLongevityEffect[] $geneToLongevityEffects
 * @property GeneToOntology[] $geneToOntologies
 * @property GeneToProgeria[] $geneToProgerias
 * @property GeneToProteinActivity[] $geneToProteinActivities
 * @property GeneToProteinClass[] $geneToProteinClasses
 * @property LifespanExperiment[] $lifespanExperiments
 * @property ProteinToGene[] $proteinToGenes
 * @property ProteinToGene[] $proteinToGenes0
 * @property GeneToAdditionalEvidence[] $geneToAdditionalEvidences
 */
class Gene extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gene';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ncbi_id', 'locationStart', 'locationEnd', 'orientation', 'rating', 'isHidden', 'created_at', 'updated_at', 'age_id', 'expressionChange'], 'integer'],
            [['protein_complex_ru', 'protein_complex_en','summary_ru','summary_en', 'ensembl', 'human_protein_atlas', 'source'], 'string'],
            [['symbol', 'aliases', 'name', 'uniprot', 'band', 'accPromoter', 'accOrf', 'accCds'], 'string', 'max' => 120],
            [['why', 'references', 'orthologs'], 'string', 'max' => 1000],
            [['commentEvolution', 'commentFunction', 'commentCause', 'commentAging', 'commentEvolutionEN', 'commentFunctionEN', 'commentAgingEN'], 'string', 'max' => 1500],
            [['commentsReferenceLinks'], 'string', 'max' => 2000],
            [['age_id'], 'exist', 'skipOnError' => true, 'targetClass' => Phylum::class, 'targetAttribute' => ['age_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'symbol' => 'Symbol',
            'aliases' => 'Aliases',
            'name' => 'Name',
            'ncbi_id' => 'Entrez Gene',
            'uniprot' => 'Uniprot',
            'why' => 'Why',
            'band' => 'Band',
            'locationStart' => 'Location Start',
            'locationEnd' => 'Location End',
            'orientation' => 'Orientation',
            'accPromoter' => 'Acc Promoter',
            'accOrf' => 'Acc Orf',
            'accCds' => 'Acc Cds',
            'references' => 'References',
            'orthologs' => 'Orthologs',
            'commentEvolution' => 'Comment Evolution',
            'commentFunction' => 'Comment Function',
            'commentCause' => 'Comment Cause',
            'commentAging' => 'Comment Aging',
            'commentEvolutionEN' => 'Comment Evolution En',
            'commentFunctionEN' => 'Comment Function En',
            'commentAgingEN' => 'Comment Aging En',
            'commentsReferenceLinks' => 'Comments Reference Links',
            'rating' => 'Rating',
            'isHidden' => 'Is Hidden',
            'expressionChange' => 'Expression Change',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'age_id' => 'Age ID',
            'protein_complex_ru' => 'Protein Complex Ru',
            'protein_complex_en' => 'Protein Complex En',
            'summary_ru' => 'Summary Ru',
            'summary_en' => 'Summary En',
            'ensembl' => 'Ensembl',
            'human_protein_atlas' => 'Human Protein Atlas',
            'source' => 'Source',
        ];
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
    public function getAge()
    {
        return $this->hasOne(Phylum::class, ['id' => 'age_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneExpressionInSamples()
    {
        return $this->hasMany(GeneExpressionInSample::class, ['gene_id' => 'id']);
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
    public function getGeneToCommentCauses()
    {
        return $this->hasMany(GeneToCommentCause::class, ['gene_id' => 'id']);
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
    public function getGeneToOntologies()
    {
        return $this->hasMany(GeneToOntology::class, ['gene_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneToProgerias()
    {
        return $this->hasMany(GeneToProgeria::class, ['gene_id' => 'id']);
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
    public function getGeneToProteinClasses()
    {
        return $this->hasMany(GeneToProteinClass::class, ['gene_id' => 'id']);
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
    public function getGeneToAdditionalEvidences()
    {
        return $this->hasMany(GeneToAdditionalEvidence::class, ['gene_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return GeneQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GeneQuery(get_called_class());
    }

    public function beforeSave($insert): bool
    {
        $this->aliases = str_replace(',', '', $this->aliases);
        return parent::beforeSave($insert);
    }
}
