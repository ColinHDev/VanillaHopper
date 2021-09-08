<?php

namespace ColinHDev\VanillaHopper\events;

use ColinHDev\VanillaHopper\blocks\Hopper;
use pocketmine\block\Jukebox;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Record;

class HopperTransferJukeboxEvent extends BlockEvent implements Cancellable {
    use CancellableTrait;

    private Record $record;
    private Jukebox $jukebox;
    private bool $isPushing;

    /**
     * @param bool $isPushing true if the record is pushed into the jukebox, false if the record is pulled from the jukebox
     */
    public function __construct(Hopper $hopper, Record $record, Jukebox $jukebox, bool $isPushing) {
        parent::__construct($hopper);
        $this->record = $record;
        $this->jukebox = $jukebox;
        $this->isPushing = $isPushing;
    }

    public function getRecord() : Record {
        return clone $this->record;
    }

    public function setRecord(Record $record) : void {
        $this->record = clone $record;
    }

    public function getJukebox() : Jukebox {
        return $this->jukebox;
    }

    public function isPushing() : bool {
        return $this->isPushing;
    }
}