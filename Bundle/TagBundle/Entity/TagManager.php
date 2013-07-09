<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\EntityManager;

class TagManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $tagClass;

    /**
     * @var string
     */
    protected $taggingClass;

    public function __construct(EntityManager $em, $tagClass, $taggingClass)
    {
        $this->em = $em;

        $this->tagClass = $tagClass;
        $this->taggingClass = $taggingClass;
    }

    /**
     * Adds a tag on the given taggable resource
     *
     * @param Tag       $tag        Tag object
     * @param Taggable  $resource   Taggable resource
     */
    public function addTag(Tag $tag, Taggable $resource)
    {
        $resource->getTags()->add($tag);
    }

    /**
     * Adds multiple tags on the given taggable resource
     *
     * @param Tag[]     $tags       Array of Tag objects
     * @param Taggable  $resource   Taggable resource
     */
    public function addTags(array $tags, Taggable $resource)
    {
        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                $this->addTag($tag, $resource);
            }
        }
    }

    /**
     * Removes an existant tag on the given taggable resource
     *
     * @param Tag       $tag        Tag object
     * @param Taggable  $resource   Taggable resource
     * @return Boolean
     */
    public function removeTag(Tag $tag, Taggable $resource)
    {
        return $resource->getTags()->removeElement($tag);
    }

    /**
     * Replaces all current tags on the given taggable resource
     *
     * @param Tag[]     $tags       Array of Tag objects
     * @param Taggable  $resource   Taggable resource
     */
    public function replaceTags(array $tags, Taggable $resource)
    {
        $resource->getTags()->clear();
        $this->addTags($tags, $resource);
    }

    /**
     * Loads or creates a tag from tag name
     *
     * @param array  $name  Tag name
     * @return Tag
     */
    public function loadOrCreateTag($name)
    {
        $tags = $this->loadOrCreateTags(array($name));
        return $tags[0];
    }

    /**
     * Loads or creates multiples tags from a list of tag names
     *
     * @param array  $names   Array of tag names
     * @return Tag[]
     */
    public function loadOrCreateTags(array $names)
    {
        if (empty($names)) {
            return array();
        }

        $names = array_unique($names);

        $builder = $this->em->createQueryBuilder();

        $tags = $builder
            ->select('t')
            ->from($this->tagClass, 't')

            ->where($builder->expr()->in('t.name', $names))

            ->getQuery()
            ->getResult()
        ;

        $loadedNames = array();
        foreach ($tags as $tag) {
            $loadedNames[] = $tag->getName();
        }

        $missingNames = array_udiff($names, $loadedNames, 'strcasecmp');
        if (sizeof($missingNames)) {
            foreach ($missingNames as $name) {
                $tag = $this->createTag($name);
                $this->em->persist($tag);

                $tags[] = $tag;
            }

            $this->em->flush();
        }

        return $tags;
    }

    /**
     * Saves tags for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     */
    public function saveTagging(Taggable $resource)
    {
        $oldTags = $this->getTagging($resource);
        $newTags = $resource->getTags();
        $tagsToAdd = $newTags;

        if ($oldTags !== null and is_array($oldTags) and !empty($oldTags)) {
            $tagsToRemove = array();

            foreach ($oldTags as $oldTag) {
                if ($newTags->exists(function ($index, $newTag) use ($oldTag) {
                    return $newTag->getName() == $oldTag->getName();
                })) {
                    $tagsToAdd->removeElement($oldTag);
                } else {
                    $tagsToRemove[] = $oldTag->getId();
                }
            }

            if (sizeof($tagsToRemove)) {
                $builder = $this->em->createQueryBuilder();
                $builder
                    ->delete($this->taggingClass, 't')
                    ->where('t.tag_id')
                    ->where($builder->expr()->in('t.tag', $tagsToRemove))
                    ->andWhere('t.resourceType = :resourceType')
                    ->setParameter('resourceType', $resource->getTaggableType())
                    ->andWhere('t.resourceId = :resourceId')
                    ->setParameter('resourceId', $resource->getTaggableId())
                    ->getQuery()
                    ->getResult()
                ;
            }
        }

        foreach ($tagsToAdd as $tag) {
            $this->em->persist($tag);
            $this->em->persist($this->createTagging($tag, $resource));
        }

        if (count($tagsToAdd)) {
            $this->em->flush();
        }
    }

    /**
     * Loads all tags for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     */
    public function loadTagging(Taggable $resource)
    {
        $tags = $this->getTagging($resource);
        $this->replaceTags($tags, $resource);
    }

    /**
     * Gets all tags for the given taggable resource
     *
     * @param Taggable  $resource   Taggable resource
     */
    protected function getTagging(Taggable $resource)
    {
        return $this->em
            ->createQueryBuilder()

            ->select('t')
            ->from($this->tagClass, 't')

            ->innerJoin('t.tagging', 't2', Expr\Join::WITH, 't2.resourceId = :id AND t2.resourceType = :type')
            ->setParameter('id', $resource->getTaggableId())
            ->setParameter('type', $resource->getTaggableType())

            // ->orderBy('t.name', 'ASC')

            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Deletes all tagging records for the given taggable resource
     *
     * @param Taggable $resource
     * @return $this
     */
    public function deleteTagging(Taggable $resource)
    {
        $taggingList = $this->em->createQueryBuilder()
            ->select('t')
            ->from($this->taggingClass, 't')

            ->where('t.entityName = :entityName')
            ->setParameter('entityName', get_class($resource))

            ->andWhere('t.recordId = :id')
            ->setParameter('id', $resource->getTaggableId())

            ->getQuery()
            ->getResult();

        foreach ($taggingList as $tagging) {
            $this->em->remove($tagging);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Returns an array of tag names for the given Taggable resource.
     *
     * @param Taggable  $resource   Taggable resource
     * @return array
     */
    public function getTagNames(Taggable $resource)
    {
        $names = array();

        if (sizeof($resource->getTags()) > 0) {
            foreach ($resource->getTags() as $tag) {
                $names[] = $tag->getName();
            }
        }

        return $names;
    }

    /**
     * Creates a new Tag object
     *
     * @param string    $name   Tag name
     * @return Tag
     */
    protected function createTag($name)
    {
        return new $this->tagClass($name);
    }

    /**
     * Creates a new Tagging object
     *
     * @param Tag       $tag        Tag object
     * @param Taggable  $resource   Taggable resource object
     * @return Tagging
     */
    protected function createTagging(Tag $tag, Taggable $resource)
    {
        return new $this->taggingClass($tag, $resource);
    }
}
