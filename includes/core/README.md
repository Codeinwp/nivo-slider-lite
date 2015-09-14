### Add plugin-core to Dev7studios Plugin

```
git subtree add --prefix includes/core https://github.com/Dev7studios/plugin-core.git master --squash 
```

### Update plugin-core from within Dev7studios Plugin

```
git subtree pull --prefix includes/core https://github.com/Dev7studios/plugin-core.git master --squash -m "Update plugin-core"
```