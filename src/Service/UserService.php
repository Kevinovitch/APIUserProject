<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Function to get all the users
     *
     * @return array
     */
    public function fetchAllUsers(): array
    {
       return $this->entityManager->getRepository(User::class)->findAll();
    }

    /***
     *
     * Function to get one user by its id
     *
     * @param $userId
     * @return User|mixed|object|null
     */
    public function fetchUserById($userId)
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        // If there is no user which has the id equals to $userId
        // We return an exception
        if (null === $user){
            throw new NotFoundHttpException("There is no user corresponding to this id");
        }

        return $user;
    }

    /***
     *
     *  Function to save into the database a newly created user
     *
     *
     * @param User $user
     * @return void
     */
    public function saveUserCreated(User $user) : void
    {
        // We set the creationdate and the updatedate
        $now = new \DateTime('now');
        $user->setCreationdate($now);
        $user->setUpdatedate($now);

        // We save the new user in the database
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Function to save into the database all the updates done
     * on one User entity
     *
     * @param User $user
     * @return void
     */
    public function saveUserUpdated(User $user): void
    {
        // We set the new updatedate to "Now"
        $user->setUpdatedate(new \DateTime('now'));

        // We flush directly be the entityManager already knows this User
        // because it has been already present in the database
        $this->entityManager->flush();
    }

    /**
     * Function to suppress a user in the database
     *
     * @param User $user
     * @return void
     */
    public function deleteUser(User $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

    }
}

