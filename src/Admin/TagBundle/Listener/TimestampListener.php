<?php

/*
 * Tag Bundle
 * This file is part of the Admin.
 *
 * (c) Victoria Lasso
 *
 */

namespace Admin\TagBundle\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;

class TimestampListener {

    public function onFlush(OnFlushEventArgs $args) {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entities = array_merge(
                $uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            if (!(get_class($entity) == 'Admin\TagBundle\Entity\Tag')) {
                continue;
            }

            $taggedEntities = array(
                array('taggedPage' => $entity->getPages(), 'classMetadata' => 'Admin\PageBundle\Entity\Page'),
                array('taggedPage' => $entity->getBlogs(), 'classMetadata' => 'Admin\BlogBundle\Entity\Blog')
            );


            foreach ($taggedEntities as $taggedEntity) {

                foreach ($taggedEntity['taggedPage'] as $taggedPage) {

                    $taggedPage->setDateLastModified($entity->getDateLastModified());

                    $em->persist($taggedPage);
                    $md = $em->getClassMetadata($taggedEntity['classMetadata']);
                    $uow->recomputeSingleEntityChangeSet($md, $taggedPage);
                }
            }
        }
    }

}
