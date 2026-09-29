<?php

namespace App\Diagnostics;

final class CheckResult
{
    /**
     * @param  CheckResult[]  $children
     */
    public function __construct(
        public readonly CheckGroup $group,
        public readonly string $label,
        public readonly CheckStatus $status,
        public readonly string $value,
        public readonly ?string $detail = null,
        public readonly array $children = [],
    ) {}

    public static function pass(CheckGroup $group, string $label, string $value, ?string $detail = null): self
    {
        return new self($group, $label, CheckStatus::Pass, $value, $detail);
    }

    public static function warn(CheckGroup $group, string $label, string $value, ?string $detail = null): self
    {
        return new self($group, $label, CheckStatus::Warn, $value, $detail);
    }

    public static function fail(CheckGroup $group, string $label, string $value, ?string $detail = null): self
    {
        return new self($group, $label, CheckStatus::Fail, $value, $detail);
    }

    public static function skipped(CheckGroup $group, string $label, string $value, ?string $detail = null): self
    {
        return new self($group, $label, CheckStatus::Skipped, $value, $detail);
    }

    /**
     * Return a new instance with the given children attached. The original instance is left untouched.
     */
    public function withChildren(CheckResult ...$children): self
    {
        return new self($this->group, $this->label, $this->status, $this->value, $this->detail, $children);
    }

    /**
     * @return array{group: string, label: string, status: string, value: string, detail: ?string, children: array}
     */
    public function toArray(): array
    {
        return [
            'group' => $this->group->value,
            'label' => $this->label,
            'status' => $this->status->value,
            'value' => $this->value,
            'detail' => $this->detail,
            'children' => array_map(static fn (CheckResult $child) => $child->toArray(), $this->children),
        ];
    }
}
