<?php

namespace App\Entity;

use App\Repository\UserSubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: UserSubscriptionRepository::class)]
#[ORM\Table(name: 'user_subscriptions')]
class UserSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userSubscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userSubscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private Subscription $subscription;

    #[ORM\Column(type: Types::SMALLINT, enumType: UserSubscriptionStatus::class)]
    private UserSubscriptionStatus $status;

    #[ORM\Column]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $endDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(Subscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getStatus(): UserSubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(UserSubscriptionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }
}
