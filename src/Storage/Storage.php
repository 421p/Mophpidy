<?php

namespace Mophpidy\Storage;

use Doctrine\ORM\EntityManager;
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