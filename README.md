## VanillaHopper
In pm4, hopper blocks were implemented to have an inventory. But the logic for pushing, pulling and picking up items was missing nonetheless.
This plugin aims to add this logic to the hopper.

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