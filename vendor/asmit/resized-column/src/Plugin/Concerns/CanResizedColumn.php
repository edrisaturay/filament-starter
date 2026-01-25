<?php

namespace Asmit\ResizedColumn\Plugin\Concerns;

trait CanResizedColumn
{
    protected bool $isPreserveOnDbEnabled = false;

    protected bool $isPreserveOnSessionEnabled = true;

    public function preserveOnDB(bool $condition = true): self
    {
        $this->isPreserveOnDbEnabled = $condition;

        return $this;
    }

    public function isPreserveOnDBEnabled(): bool
    {
        return $this->isPreserveOnDbEnabled;
    }

    public function preserveOnSession(bool $condition = true): self
    {
        $this->isPreserveOnSessionEnabled = $condition;

        return $this;
    }

    public function isPreserveOnSessionEnabled(): bool
    {
        return $this->isPreserveOnSessionEnabled;
    }
}
