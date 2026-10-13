# Optional integrations

Optional plugins in this directory are maintained in the **tainacan-theme** repository. They are installed separately in WordPress; they are not loaded automatically by the theme and do not require a separate GitHub repository.

## Tainacan DearFlip

See [`tainacan-dearflip/README.md`](tainacan-dearflip/README.md) for dependencies, configuration and testing. The integration depends on the attachment viewer extension points proposed separately in the Tainacan plugin.

To package it for **Plugins → Add Plugin → Upload Plugin**, run in this directory using PowerShell:

```powershell
New-Item -ItemType Directory -Path dist -Force | Out-Null
Compress-Archive -LiteralPath tainacan-dearflip -DestinationPath dist/tainacan-dearflip.zip -Force
```

The ZIP has a `tainacan-dearflip/` root directory. Build artifacts under `dist/` are ignored by Git. In a release, the plugin ZIP can be published alongside the theme artifact from the same repository. The normal theme build copies `src/` only and does not include this directory in the installed theme.
