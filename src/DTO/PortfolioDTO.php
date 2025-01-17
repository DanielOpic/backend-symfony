<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PortfolioDTO
{
    /**
     * @Assert\NotBlank(message="Name cannot be blank.")
     */
    public string $name;

    /**
     * @Assert\NotBlank(message="Link cannot be blank.")
     */
    public string $link;

    /**
     * @Assert\NotBlank(message="From date cannot be blank.")
     * @Assert\Date(message="From date must be a valid date.")
     */
    public string $fromdate;

    /**
     * @Assert\NotBlank(message="Description cannot be blank.")
     */
    public string $description;
}
