<?php

namespace app\models\common;

use Yii;

/**
 * This is the model class for table "process_localization".
 *
 * @property int $id
 * @property string $name_en
 * @property string $name_ru
 * @property int $created_at
 * @property int $updated_at
 *
 * @property GeneToProteinActivity[] $geneToProteinActivities
 */
class ProcessLocalization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'process_localization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['name_en', 'name_ru'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_en' => 'Name En',
            'name_ru' => 'Name Ru',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeneToProteinActivities()
    {
        return $this->hasMany(GeneToProteinActivity::class, ['process_localization_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ProcessLocalizationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProcessLocalizationQuery(get_called_class());
    }
}
