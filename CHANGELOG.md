# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.3.0 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.2 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2016-10-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#27](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/27)
  fixes the `SwaggerUiControllerFactory` to properly use the `$container`
  variable, not the nonexistent `$services` variable, when pulling the
  `ApiFactory` to inject in the controller.

## 1.2.0 - 2016-07-14

### Added

- [#24](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/24)
  adds support for v3 releases of Zend Framework components, keeping
  compatibility for v2 releases.
- [#14](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/14) and
  [#19](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/19) add
  support for retrieving the field type as both the type and dataType.

### Deprecated

- Nothing.

### Removed

- [#24](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/24)
  removes support for PHP 5.5.
- [#21](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/21)
  removes the verbiage "Operation for {Api}" as a default service description
  from the swagger templates.

### Fixed

- [#23](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/23)
  updates the link to the Swagger website to point to the new swagger.io URL.
