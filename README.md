## VanillaHopper
In pm4, hopper blocks were implemented to have an inventory. But the logic for pushing, pulling and picking up items was missing nonetheless.
This plugin aims to add this logic to the hopper.

### Optimizations
Normally a hopper should run a block update every tick to reduce and check its cooldown if it has expired.
Because it is highly inefficient to update all loaded hoppers every tick, just for letting them reduce their cooldown by one, the block update of hoppers is always scheduled to the expiration of their cooldown and not directly the next tick.
To prevent any issues with the cooldown, hoppers are saving in which tick they were lastly updated to prevent them from updating too early.

### Customizations
Customizations can be done in the `config.yml` in the plugin's `plugin_data` folder:
- `hopper.transferCooldown`: `8`
  - The default cooldown of hoppers in Minecraft is 8 ticks. To in- or decrease the cooldown, you can just edit this number.
- `hopper.itemsPerUpdate`: `1`
  - Normally a hopper only pushes one item and pulls or picks up one per update. You can specify how many items a hopper will try to push, pull or pick up when updated.
    This can be useful if you increased the cooldown of hoppers but want to keep the same "item per tick" ratio.
- `hopper.updatesPerTick`: `0`
  - By default, there is no limit on how many block updates can be scheduled per tick.
    As it would be very performance costly to have scheduled hundreds of hopper updates scheduled on the same tick, you can change this number, to limit the number of hopper updates that are allowed to be scheduled per tick.
    If a hopper update would be scheduled on a tick that is already on the max value, the update is scheduled on the next tick that's not on the max value.

### FAQ
#### What are your sources?
Every information about the logic for pushing, pulling and picking up items came from the [minecraft fandom wiki](https://minecraft.fandom.com/wiki/Hopper).

#### Why are there so many comments in the code?
Minecraft's hopper logic is very complex. To prevent anybody from getting confused about how certain things were done, most parts were commented to explain what was done and why.

#### Why not create a [PR](https://github.com/pmmp/PocketMine-MP/pull/4416)?
I did, but it was stated that hopper logic won't be implemented in pm4 and because I didn't want to maintain a PR for the time till pm5, I closed it.
Still, I wanted to use that logic in a plugin to use it myself and therefore I created this.

### Tests
- Functionality tests
  - [Every block a hopper can push items into was tested if it works as explained in the sources.](https://www.youtube.com/watch?v=4gSyuViaPaU)
  - [Every block a hopper can pull items from was tested if it works as explained in the sources.](https://www.youtube.com/watch?v=6NWvr6Kv88E)
  - [The collision box of the area where the hopper should pick up items was checked and works as explained in the sources.](https://www.youtube.com/watch?v=hVEPiK9KWkA)

- "Performance" tests
  - 128 hoppers pushing 27 * 64 dirt from one chest to another ([Timings](https://timings.pmmp.io/?id=158627)):
    ![Performance Test](https://user-images.githubusercontent.com/54852588/131256515-3611c594-08e1-45a1-8bd2-3ebbaf141c8a.png)

### TODO
#### Make pushing and pulling more modular
Currently, the pushing and pulling methods are not very developer friendly when it comes to customizations, since it is hard to access and overwrite the `Hopper::push()` and `Hopper::pull()` methods without beeing forced to copy code.
This could be done by implementing a behaviour system which lets developers register custom behaviours for any block.
Since this would include rewriting some parts of the existing core, which would cost much time, what I do not see benefitial at the moment, PLEASE create an issue, if you find yourself needing a better implementation. Till then, this will stay as a TODO.

#### Implementing entity pulling
Normally, hoppers can not only pull items from blocks, but from entities like minecarts too.
But since PocketMine-MP does not support them, there is no point in implementing this, since this would be out of the scope of this plugin.
Although it should be at least possible to let hoppers also scan for entities when pulling, which is not possible with the current system.

#### Supporting more blocks
Composters, Brewing Stands and Jukeboxes are currently either not or just poorly supported. This should be changed.
But since PocketMine-MP itself does not implement these blocks correctly, there is no reason for us at the moment of writing this.

### For developers
#### Event handling
- Through the different events, you can easily implement your own rules for hoppers.
  Handle these events by simply creating an ordinary listener (`class EventListener implements Listener`) in your plugin and import (`use` keyword) them by their namespace (`event name`: `namespace`).
- `BlockItemPickupEvent`: `pocketmine\event\block\BlockItemPickupEvent`
  - This event is called when a hopper tries to pick up an item.
- `HopperEvent`: `ColinHDev\VanillaHopper\events\HopperEvent`
  - This event is called when a hopper either tries to push or pull an item.
- `HopperPushEvent`: `ColinHDev\VanillaHopper\events\HopperPushEvent`
  - This event is called when a hopper tries to push an item.
- `HopperPushContainerEvent`: `ColinHDev\VanillaHopper\events\HopperPushContainerEvent`
  - This event is called when a hopper tries to push an item into a block's inventory.
- `HopperPushJukeboxEvent`: `ColinHDev\VanillaHopper\events\HopperPushJukeboxEvent`
  - This event is called when a hopper tries to push a record into a jukebox.
- `HopperPullEvent`: `ColinHDev\VanillaHopper\events\HopperPullEvent`
  - This event is called when a hopper tries to pull an item.
- `HopperPullContainerEvent`: `ColinHDev\VanillaHopper\events\HopperPullContainerEvent`
  - This event is called when a hopper tries to pull an item from a block's inventory.