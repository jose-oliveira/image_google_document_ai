# Image Google Document AI

## Contents of this file

* Introduction
* Installation
* Configuration

## Introduction

This module provides an image field type that allows editors to automatically
fill field values in the current entity with data from the uploaded image using
Google's [Cloud Document AI API](https://cloud.google.com/document-ai/docs/reference/rest).

## Installation

Install this module as you would normally install a
contributed Drupal module. Visit
[https://www.drupal.org/node/1897420](https://www.drupal.org/node/1897420) for
further information.

## Configuration

Use the configuration settings (Administration > Web Services > Image Google
Document AI) to setup your credentials and project information.

Add a field with type "Google Document AI Image" to your entity type and label
your fields with the expected labels to be extracted from the document.
