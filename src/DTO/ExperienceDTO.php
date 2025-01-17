<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ExperienceDTO
{
    /**
     * @Assert\NotBlank(message="Name cannot be blank.")
     */
    public string $name;

    /**
     * @Assert\NotBlank(message="Company cannot be blank.")
     */
    public string $company;

    /**
     * @Assert\NotBlank(message="From date cannot be blank.")
     * @Assert\Date(message="From date must be a valid date.")
     */
    public string $fromdate;

    /**
     * @Assert\NotBlank(message="To date cannot be blank.")
     * @Assert\Date(message="To date must be a valid date.")
     */
    public string $todate;

    /**
     * @Assert\Type(type="bool", message="Current must be a boolean.")
     */
    public bool $current;

    /**
     * @Assert\NotBlank(message="Description cannot be blank.")
     */
    public string $description;
}
