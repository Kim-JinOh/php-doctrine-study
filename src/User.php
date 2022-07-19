<?php
class User
{
    private $banned;
    private $username;
    private $passwordHash;
    private $bans;

    public function toNickname(): string
    {
        return $this->username;
    }

    public function authenticate(string $password, callable $checkHash): bool
    {
        return $checkHash($password, $this->passwordHash) && ! $this->hasActiveBans();
    }

    public function changePassword(string $password, callable $hash): void
    {
        $this->passwordHash = $hash($password);
    }

    public function ban(\DateInterval $duration): void
    {
        assert($duration->invert !== 1);

        $this->bans[] = new Ban($this);
    }
}