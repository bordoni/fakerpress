## Quick How To
Below you will find a quick how to Update one of the `vendor` libraries

They were **added** using:

```bash
git subtree add --prefix inc/vendor/repoName \
  git@github.com:user/repoName.git master --squash
```

To **update** the vendor library with the latest changes:

```bash
git subtree pull --prefix inc/vendor/repoName \
  git@github.com:user/repoName.git master --squash
```

And if you have changes you want to **upstream**, do:

```bash
git subtree push --prefix inc/vendor/repoName \
  git@github.com:user/repoName.git master --squash
```

- - -

## List of dependencies
* [Faker](https://github.com/fzaninotto/Faker)
