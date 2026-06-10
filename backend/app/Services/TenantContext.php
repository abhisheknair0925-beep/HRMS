<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\Branch;

class TenantContext
{
    protected ?Company $company = null;
    protected ?Branch $branch = null;

    /**
     * Set the current active Company.
     *
     * @param Company $company
     * @return void
     */
    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    /**
     * Get the current active Company.
     *
     * @return Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * Get the active Company UUID.
     *
     * @return string|null
     */
    public function getCompanyId(): ?string
    {
        return $this->company?->id;
    }

    /**
     * Set the current active Branch.
     *
     * @param Branch $branch
     * @return void
     */
    public function setBranch(Branch $branch): void
    {
        $this->branch = $branch;
    }

    /**
     * Get the current active Branch.
     *
     * @return Branch|null
     */
    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    /**
     * Get the active Branch UUID.
     *
     * @return string|null
     */
    public function getBranchId(): ?string
    {
        return $this->branch?->id;
    }
}
