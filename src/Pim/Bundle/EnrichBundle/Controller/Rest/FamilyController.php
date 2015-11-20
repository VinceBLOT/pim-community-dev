<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\EnrichBundle\Entity\Repository\FamilySearchableRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var EntityRepository */
    protected $familyRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FamilySearchableRepository */
    protected $familySearchableRepo;

    /**
     * @param EntityRepository           $familyRepository
     * @param NormalizerInterface        $normalizer
     * @param FamilySearchableRepository $familySearchableRepo
     */
    public function __construct(
        EntityRepository $familyRepository,
        NormalizerInterface $normalizer,
        FamilySearchableRepository $familySearchableRepo
    ) {
        $this->familyRepository     = $familyRepository;
        $this->normalizer           = $normalizer;
        $this->familySearchableRepo = $familySearchableRepo;
    }

    /**
     * Get the family collection
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $query      = $request->query;
        $search     = $query->get('search');

        $families = $this->familySearchableRepo->findBySearch($search, ['limit' => 20]);

        $normalizedFamilies = [];
        foreach ($families as $family) {
            $normalizedFamilies[$family->getCode()] = $this->normalizer->normalize($family, 'json');
        }

        return new JsonResponse($normalizedFamilies);
    }

    /**
     * Get a single family
     *
     * @param int $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $family = $this->familyRepository->findOneByCode($identifier);

        if (!$family) {
            throw new NotFoundHttpException(sprintf('Family with code "%s" not found', $identifier));
        }

        $normalizedFamily = [];
        return new JsonResponse(
            $normalizedFamily[$family->getCode()] = $this->normalizer->normalize($family, 'json')
        );
    }
}
