<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscription;
use App\Entity\UserSubscriptionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSubscription>
 *
 * @method UserSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSubscription[]    findAll()
 * @method UserSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSubscription::class);
    }

    public function findExistingActiveUserSubscription(
        User $user,
        Subscription $subscription
    ): ?UserSubscription {
        $qb = $this->createQueryBuilder('us');

        $qb->andWhere('us.user = :user')
            ->setParameter('user', $user)
            ->andWhere('us.subscription = :subscription')
            ->setParameter('subscription', $subscription)
            ->andWhere('us.status = :status')
            ->setParameter('status', UserSubscriptionStatus::ACTIVE)
        ;

        $userSubscription = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);

        return $userSubscription instanceof UserSubscription ? $userSubscription : null;
    }
}
