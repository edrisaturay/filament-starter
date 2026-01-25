#!/bin/bash

# Simple release script for tagging
VERSION=$1

if [ -z "$VERSION" ]; then
    echo "Usage: ./release.sh v1.0.0"
    exit 1
fi

echo "Releasing version $VERSION..."

# Ensure we are in the package directory or handle git accordingly
# This is a template script.

# git tag -a $VERSION -m "Release $VERSION"
# git push origin $VERSION

echo "Done."
