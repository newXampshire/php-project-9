<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Url extends Model
{
    private int $id;
    private string $name;
    private DateTimeImmutable $createdAt;

    private ?UrlCheck $urlCheck = null;

    public function __set(string $name, $value): void
    {
        $value = $this->prepareValue($value);

        if (str_starts_with($name, 'uc')) {
            if ($value === null) {
                return;
            }

            $method = $this->generateMethodName(explode('.', $name)[1]);

            $urlCheck = $this->getUrlCheck();
            if ($urlCheck === null) {
                $urlCheck = new UrlCheck();
                $this->setUrlCheck($urlCheck);
            }
            $urlCheck->$method($value);

            return;
        }

        $method = $this->generateMethodName($name);

        $this->$method($value);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUrlCheck(): ?UrlCheck
    {
        return $this->urlCheck;
    }

    public function setUrlCheck(UrlCheck $urlCheck): self
    {
        $this->urlCheck = $urlCheck;

        return $this;
    }
}
