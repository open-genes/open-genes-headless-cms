<?php
namespace genes\application\service;

use genes\application\dto\FunctionalClusterDto;
use genes\application\dto\GeneFullViewDto;
use genes\application\dto\GeneListViewDto;
use genes\application\dto\LatestGeneViewDto;
use genes\infrastructure\dataProvider\GeneDataProviderInterface;
use genes\infrastructure\dataProvider\GeneExpressionDataProviderInterface;
use yii\base\Exception;

class GeneInfoService implements GeneInfoServiceInterface
{
    /** @var GeneDataProviderInterface  */
    private $geneDataProvider;
    /** @var GeneExpressionDataProviderInterface */
    private $geneExpressionDataProvider;

    public function __construct(
        GeneDataProviderInterface $geneRepository,
        GeneExpressionDataProviderInterface $geneExpressionDataProvider)
    {
        $this->geneDataProvider = $geneRepository;
        $this->geneExpressionDataProvider = $geneExpressionDataProvider;
    }

    /**
     * @inheritDoc
     */
    public function getGeneViewInfo(int $geneId, string $lang = 'en-US'): GeneFullViewDto
    {
        $geneArray = $this->geneDataProvider->getGene($geneId);

        $geneDto = $this->mapViewDto($geneArray, $lang);
        $geneDto->expression = $this->geneExpressionDataProvider->getByGeneId($geneId, $lang);
        return $geneDto;
    }

    /**
     * @inheritDoc
     */
    public function getLatestGenes(int $count, string $lang = 'en-US'): array
    {
        $latestGenesArray = $this->geneDataProvider->getLatestGenes($count);
        $geneDtos = [];
        foreach ($latestGenesArray as $latestGene) {
            $geneDtos[] = $this->mapLatestViewDto($latestGene);
        }

        return $geneDtos;
    }
    /**
     * @inheritDoc
     */
    public function getAllGenes(int $count = null, string $lang = 'en-US'): array
    {
        $latestGenesArray = $this->geneDataProvider->getAllGenes($count);
        $geneDtos = [];
        foreach ($latestGenesArray as $latestGene) {
            $geneDtos[] = $this->mapListViewDto($latestGene, $lang);
        }

        return $geneDtos;
    }

    public function getByFunctionalClustersIds(array $functionalClustersIds, string $lang = 'en-US'): array
    {
        $genesArray = $this->geneDataProvider->getByFunctionalClustersIds($functionalClustersIds);
        $geneDtos = [];
        foreach ($genesArray as $gene) {
            $geneDtos[] = $this->mapListViewDto($gene, $lang);
        }

        return $geneDtos;
    }

    public function getByExpressionChange(string $expressionChange, string $lang = 'en-US'): array
    {
        $genesArray = $this->geneDataProvider->getByExpressionChange($this->prepareExpressionChangeForQuery($expressionChange));
        $geneDtos = [];
        foreach ($genesArray as $gene) {
            $geneDtos[] = $this->mapListViewDto($gene, $lang);
        }

        return $geneDtos;
    }

    protected function mapViewDto(array $geneArray, string $lang): GeneFullViewDto
    {
        $geneDto = new GeneFullViewDto();
        $geneCommentsReferenceLinks = [];
        $geneCommentsReferenceLinksSource = explode(',', $geneArray['commentsReferenceLinks']);
        foreach ($geneCommentsReferenceLinksSource as $commentsRef) {
            $commentsRefLink = preg_replace('/^(\s?<br>)?\s*\[[0-9\-]*\s*[[0-9\-]*]\s*/', '', $commentsRef);
            $geneCommentsReferenceLinks[$commentsRefLink] = $commentsRef;
        }
        $geneDto->id = (int)$geneArray['id'];
        $geneDto->ageMya = $geneArray['age_mya'];
        $geneDto->agePhylo = $geneArray['age_phylo'];
        $geneDto->symbol = $geneArray['symbol'];
        $geneDto->aliases = explode(' ', $geneArray['aliases']);
        $geneDto->name = $geneArray['name'];
        $geneDto->entrezGene = $geneArray['entrezGene'];
        $geneDto->uniprot = $geneArray['uniprot'];
        $geneDto->commentCause =  explode(',', $geneArray['comment_cause']);
        $geneDto->commentEvolution = $geneArray['comment_evolution'];
        $geneDto->commentFunction = $geneArray['comment_function'];
        $geneDto->commentAging = $geneArray['comment_aging'];
        $geneDto->commentsReferenceLinks = $geneCommentsReferenceLinks;
        $geneDto->rating = $geneArray['rating'];
        $geneDto->functionalClusters = $this->mapFunctionalClusterDtos($geneArray['functional_clusters']);
        $geneDto->expressionChange = $this->prepareExpressionChangeForView($geneArray['expressionChange'], $lang);

        return $geneDto;
    }

    private function mapLatestViewDto(array $geneArray): LatestGeneViewDto
    {
        $geneDto = new LatestGeneViewDto();
        $geneDto->id = (int)$geneArray['id'];
        $geneDto->ageMya = $geneArray['age_mya'];
        $geneDto->agePhylo = $geneArray['age_phylo'];
        $geneDto->symbol = $geneArray['symbol'];
        return $geneDto;
    }

    private function mapListViewDto(array $geneArray, string $lang): GeneListViewDto
    {
        $geneDto = new GeneListViewDto();
        $geneDto->id = (int)$geneArray['id'];
        $geneDto->name = $geneArray['name'];
        $geneDto->ageMya = $geneArray['age_mya'];
        $geneDto->agePhylo = $geneArray['age_phylo'];
        $geneDto->symbol = $geneArray['symbol'];
        $geneDto->entrezGene = $geneArray['entrezGene'];
        $geneDto->uniprot = $geneArray['uniprot'];
        $geneDto->expressionChange = $this->prepareExpressionChangeForView($geneArray['expressionChange'], $lang);
        $geneDto->aliases = explode(' ', $geneArray['aliases']);
        $geneDto->functionalClusters = $this->mapFunctionalClusterDtos($geneArray['functional_clusters']);
        return $geneDto;
    }

    /**
     * @param string $geneFunctionalClustersString
     * @return FunctionalClusterDto[]
     */
    private function mapFunctionalClusterDtos($geneFunctionalClustersString): array
    {
        $functionalClusterDtos = [];
        if ($geneFunctionalClustersString) {
            $functionalClustersArray = explode(',', $geneFunctionalClustersString);
            foreach ($functionalClustersArray as $functionalCluster) {
                list($id, $name) = explode('|', $functionalCluster);
                $functionalClusterDto = new FunctionalClusterDto();
                $functionalClusterDto->id = (int)$id;
                $functionalClusterDto->name = $name;
                $functionalClusterDtos[] = $functionalClusterDto;
            }
        }

        return $functionalClusterDtos;
    }

    private static $expressionChangeEn = [
        'уменьшается' => 'decreased',
        'увеличивается' => 'increased',
        'неоднозначно' => 'mixed',
    ];

    private function prepareExpressionChangeForView($expressionChange, string $lang): ?string // todo изменить в бд хранение изменения экспрессии
    {
        if(!$expressionChange || !isset(self::$expressionChangeEn[$expressionChange])) {
            return null;
        }
        return $lang == 'en-US' ? self::$expressionChangeEn[$expressionChange] : $expressionChange;
    }

    private function prepareExpressionChangeForQuery($expressionChange): ?string // todo изменить в бд хранение изменения экспрессии
    {
        if(!$expressionChange || !in_array($expressionChange, self::$expressionChangeEn)) {
            throw new Exception('invalid $expressionChange value');
        }
        return array_search($expressionChange, self::$expressionChangeEn);
    }
}