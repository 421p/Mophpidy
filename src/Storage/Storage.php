<?php

namespace Mophpidy\Storage;

use Doctrine\ORM\EntityManager;
use Mophpidy\Entity\CallbackContainer;
use Mophpidy\Entity\User;

class Storage
{
    private $em;

    /**
     * Storage constructor.
     * @param EntityManager $connection
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function __construct(EntityManager $connection)
    {
        $this->em = $connection;

        $this->updateDefaultAllowedUsers();
    }

    /**
     * @param int $id
     * @return User|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getUser(int $id): ?User
    {
        return $this->em->find(User::class, $id);
    }

    /**
     * @param CallbackContainer $container
     * @throws \Throwable
     */
    public function addCallback(CallbackContainer $container)
    {
        $this->em->transactional(
            function () use ($container) {
                $this->em->persist($container);
                $this->em->flush();
            }
        );
    }

    /**
     * @param string $id
     * @return CallbackContainer|null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getCallback(string $id): ?CallbackContainer
    {
        return $this->em->find(CallbackContainer::class, $id);
    }

    /**
     * @param CallbackContainer $container
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeCallback(CallbackContainer $container): void
    {
        $this->em->remove($container);
        $this->em->flush();
    }

    /**
     * @return User[]
     */
    public function getNotificationSubscribers(): array
    {
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.notification = true')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function disableNotifications(int $id): bool
    {
        $user = $this->em->find(User::class, $id);

        if ($user->shouldBeNotified() === false) {
            return false;
        } else {
            $user->setNotification(false);
            $this->em->flush();

            return true;
        }
    }

    /**
     * @param int $id
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function enableNotifications(int $id): int
    {
        $user = $this->em->find(User::class, $id);

        if ($user->shouldBeNotified() === true) {
            return false;
        } else {
            $user->setNotification(true);
            $this->em->flush();

            return true;
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function isUserAllowed(int $id): bool
    {
        return $this->em->find(User::class, $id) !== null;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function updateDefaultAllowedUsers()
    {
        $users = array_map('trim', explode(',', getenv('ALLOWED_USERS')));

        foreach ($users as $id) {
            $user = $this->em->find(User::class, $id);

            if ($user === null) {
                $user = new User();
                $user->setId($id);
                $user->setNotification(false);

                $this->em->persist($user);
            }
        }

        $this->em->flush();
    }
}