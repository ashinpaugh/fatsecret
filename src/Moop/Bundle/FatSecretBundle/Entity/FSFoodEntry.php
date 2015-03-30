<?php

namespace Moop\Bundle\FatSecretBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *   @ORM\Index(name="idx_entry_id", columns={"entry_id"})
 * })
 */
class FSFoodEntry
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="bigint")
     * @var Integer
     */
    protected $entry_id;
    
    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $date;
    
    /**
     * Construct.
     * 
     * @param \DateTime $date
     * @param Integer   $entry_id
     */
    function __construct($date, $entry_id)
    {
        $this->date     = $date;
        $this->entry_id = $entry_id;
    }
    
    /**
     * @ORM\PrePersist()
     */
    public function preInsert()
    {
        $this->date = new \DateTime();
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return FSFoodEntry
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * @param \DateTime $date
     *
     * @return FSFoodEntry
     */
    public function setDate($date)
    {
        $this->date = $date;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getEntryId()
    {
        return $this->entry_id;
    }
    
    /**
     * @param int $entry_id
     *
     * @return FSFoodEntry
     */
    public function setEntryId($entry_id)
    {
        $this->entry_id = $entry_id;
        
        return $this;
    }
}