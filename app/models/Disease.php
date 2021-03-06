<?php

namespace app\models;

use app\models\behaviors\ChangelogBehavior;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

/**
 * Disease represents the model behind the search form of `app\models\common\Disease`.
 */
class Disease extends \app\models\common\Disease
{
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
        return [
            [['id', 'omim_id', 'created_at', 'updated_at'], 'integer'],
            [['name_ru', 'name_en'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'omim_id' => $this->omim_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name_ru', $this->name_ru])
            ->andFilterWhere(['like', 'name_en', $this->name_en]);

        return $dataProvider;
    }

    public static function findAllAsArray()
    {
        $result = [];
        $diseases = self::find()->all();
        foreach ($diseases as $disease) {
            $result[$disease->id] = $disease->name_ru ?: $disease->name_en;
        }

        return $result;
    }

    public function getLinkedGenesIds()
    {
        return $this->getGeneToDiseases()
            ->select('gene_id')->distinct()->column();
    }
}
