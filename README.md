# Dude GitHub feed
WordPress plugin to get latest activity for GitHub user and/or repository.

Basically this plugin fetches activity feed from GitHub, saves those to transient and next requests will be served from there. After transient has expired and deleted, new fetch from GitHub will be made and saved to transient. This implements very simple cache.

Handcrafted with love at [Digitoimisto Dude Oy](http://dude.fi), a Finnish boutique digital agency in the center of Jyväskylä.

## Table of contents
1. [Please note before using](#please-note-before-using)
2. [License](#license)
3. [Usage](#usage)
  1. [Hooks](#hooks)
4. [Composer](#composer)
5. [Contributing](#contributing)

### Please note before using
This plugin is not meant to be "plugin for everyone", it needs at least some basic knowledge about php and css to add it to your site and making it look beautiful.

This is a plugin in development for now, so it may update very often.

### License
Dude GitHub feed is released under the GNU GPL 2 or later.

### Usage
This plugin does not have settings page or provide anything visible on front-end. So it's basically dumb plugin if you don't use any filters listed below.

Get user activity by calling function `dude_github_feed()->get_user_activity()`, pass username as a only argument.
Get repository activity by calling function `dude_github_feed()->get_repository_activity()`, as arguments pass repository owner and repository name.

#### Hooks
All the settings are set with filters, and there is also few filters to change basic functionality and manipulate data before caching.

##### `dude-github-feed/user_activity_transient`
Change name of the transient for user activity, must be unique for every user. Passed arguments are default name and username.

Defaults to `dude-github-user-$username`.

##### `dude-github-feed/user_activity_event_types`
Determine what kind of user activity will be saved. Only passed argument is array of default event types.

Defaults to: IssuesEvent, ForkEvent, ForkApplyEvent, GistEvent, IssueCommentEvent, PullRequestEvent, PushEvent, ReleaseEvent and WatchEvent.

Possible event types are listed in GitHub API [documentation](https://developer.github.com/v3/activity/events/types/).

##### `dude-github-feed/user_activity`
Manipulate or use data before it's cached to transient. Only passed argument is array of events.

##### `dude-github-feed/user_activity_count`
Change amount of saved events. Only passed argument is default value.

Defaults to 5.

##### `dude-github-feed/user_activity_lifetime`
Change activity cache lifetime. Only passed argument is default lifetime in seconds.

Defaults to 600 (= ten minutes).

##### `dude-github-feed/repository_activity_transient`
Change name of the transient for repository activity, must be unique for every repository. Passed arguments are default name and repository name.

Defaults to `dude-github-repo-$repository`.

##### `dude-github-feed/repository_activity_event_types`
Determine what kind of repository activity will be saved. Only passed argument is array of default event types.

Defaults to: ForkApplyEvent, IssueCommentEvent, PushEvent and ReleaseEvent.

Possible event types are listed in GitHub API [documentation](https://developer.github.com/v3/activity/events/types/).

##### `dude-github-feed/repository_activity`
Manipulate or use data before it's cached to transient. Only passed argument is array of events.

##### `dude-github-feed/repository_activity_count`
Change amount of saved events. Only passed argument is default value.

Defaults to 5.

##### `dude-github-feed/repository_activity_lifetime`
Change activity cache lifetime. Only passed argument is default lifetime in seconds.

Defaults to 600 (= ten minutes).

### Composer

To use with composer, run `composer require digitoimistodude/dude-github-feed dev-master` in your project directory or add `"digitoimistodude/dude-github-feed":"dev-master"` to your composer.json require.

### Contributing
If you have ideas about the theme or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding to the nature of that matter, please read [Please note section](#please-note-before-using). Thank you very much.