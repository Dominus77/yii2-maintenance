Console commands
================

| Command                         | Description                             |
|:------------------------------- |:--------------------------------------- |
| `php yii maintenance`           | Mode status                             |
| `php yii maintenance/enable`    | Enable mode                             |
| `php yii maintenance/update`    | Editing mode                            |
| `php yii maintenance/followers` | Alert Subscribers                       |
| `php yii maintenance/disable`   | Turn off the mode and send alerts       |

The following options are available for `enable` and `update`:

| Option      | Alias | Description                                         |
|:----------- |:----- |:--------------------------------------------------- |
| --date      |  -d   | Set/Change Maintenance End Date                     |
| --title     |  -t   | Set/Change the title on the page                    |
| --content   |  -c   | Set/Change the main content on the page             |
| --subscribe |  -s   | Show/Hide the subscription form on the page         |
| --timer     |  -tm  | Show/Hide the timer on page                         | 

Example:
```
php yii maintenance/enable -d="07-03-2020 18:00:00" -s=true -tm=true
php yii maintenance/update -t="Maintenance"
php yii maintenance/update -c="The site is undergoing technical work. We apologize for any inconvenience caused."
```