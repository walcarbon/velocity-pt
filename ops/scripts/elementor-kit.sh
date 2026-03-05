#!/usr/bin/env bash
set -euo pipefail

ACTION="${1:-export}"
KIT_PATH="elementor-kit/carbon-kit.zip"

if [[ "$ACTION" == "export" ]]; then
  wp elementor kit export "$KIT_PATH"
elif [[ "$ACTION" == "import" ]]; then
  wp elementor kit import "$KIT_PATH"
else
  echo "Usage: $0 [export|import]"
  exit 1
fi
