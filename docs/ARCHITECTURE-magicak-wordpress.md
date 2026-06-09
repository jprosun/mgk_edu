# Architecture Reference

## 1. Build Pipeline (Real Runtime Flow)

The production image is **NOT** the `Version/*/magicak-wp-layer3.v1/` folders. Those exist only as reference/visibility artifacts. The actual runtime image is built from the root `Dockerfile` (PHP 8.1) or `Dockerfile.8.4` (PHP 8.4).

```
Docker Hub (public)
  └─ php:8.4-fpm-alpine3.21
        ↓
  [Version php8.4.21-3.21/magicak-wp-layer1.v1/]
  magicak/magicak-layer1:8.4.21-3.21v1.0          ← Push to Docker Hub
  Nginx 1.26.3 (compiled) + PHP ext + MariaDB APK
        ↓
  [Version php8.4.21-3.21/magicak-wp-layer2.v1/]
  magicak/magicak-layer2:8.4.21-3.21v1.0          ← Push to Docker Hub
  Redis 7.2 + phpredis 6.0 + igbinary
        ↓
  [Root: Dockerfile.8.4]  ← CI/CD builds this
  registry.margick.com/magicak-web/magicak-wordpress:magicak-layer3-8.4-3.21v{VERSION}
  Galera + Sentinel + kubectl + wp-cli + WordPress  ← Final runtime image
```

### Layer responsibilities

| Layer | Built from | Pushed to | Purpose |
|-------|-----------|-----------|---------|
| Layer 0 | Docker Hub official | Docker Hub | PHP-FPM + Alpine base |
| Layer 1 | `Version php8.4.21-3.21/magicak-wp-layer1.v1/` | Docker Hub | Infrastructure (Nginx, PHP ext, MariaDB APK) |
| Layer 2 | `Version php8.4.21-3.21/magicak-wp-layer2.v1/` | Docker Hub | Services (Redis, phpredis) |
| Layer 3 (runtime) | Root `Dockerfile.8.4` | GitLab private registry | Application + Cluster (Galera, Sentinel, wp-cli) |
| Layer 3 (reference) | `Version php8.4.21-3.21/magicak-wp-layer3.v1/` | _(not built in CI)_ | Development visibility / inspection only |

The reference layer 3 is useful for examining WordPress config templates and nginx settings in isolation. It is never deployed.

---

## 2. Container Runtime Architecture

### Single-container mode (`PLAN_TYPE=free`)

```
Container
├─ supervisord
│   ├─ php-fpm          (priority 5) — PHP FastCGI worker pool
│   ├─ delay.sh         (priority 7) — 5s sleep; ensures FPM socket before nginx
│   ├─ nginx            (priority 10) — serves HTTP on :80
│   ├─ watch-php-fpm    — D-state process killer, restarts FPM on deadlock
│   ├─ listen-redis     — sleeps (no-op in free plan)
│   ├─ listen-redis-log — sleeps (no-op in free plan)
│   └─ galera-monitor   — monitors quorum (no-op in free plan, 1-node cluster)
├─ redis-server (background, localhost only)
└─ mariadb (standalone, no wsrep)
```

### Galera + Sentinel cluster mode (`PLAN_TYPE=business`)

```
Kubernetes StatefulSet (3+ pods)

Pod 0 (mysite-0)          Pod 1 (mysite-1)          Pod 2 (mysite-2)
├─ MariaDB (Galera)   ←wsrep→  MariaDB (Galera)   ←wsrep→  MariaDB (Galera)
│   port 3306              port 3306              port 3306
│   port 4444 (SST)        port 4444              port 4444
│   port 4567 (wsrep)      port 4567              port 4567
│   port 4568 (IST)        port 4568              port 4568
├─ Redis (master/replica via Sentinel)
│   port 6379
├─ Redis Sentinel          Redis Sentinel          Redis Sentinel
│   port 26379             port 26379              port 26379
│   (monitors redismaster-{WEBSITE_HOST})
├─ nginx → php-fpm (WordPress)
└─ galera-quorum-check.sh  (kills MariaDB if quorum lost)
```

### Pod startup sequence (business plan)

```
entrypoint.sh
├─ Detect POD_NUMBER from $HOSTNAME (last field after last hyphen)
├─ Detect NUM_REPLICAS via kubectl get statefulset
├─ Compute QUORUM = NUM_REPLICAS / 2 + 1
├─ Configure my.cnf from /tmp/my-master.cnf (sed substitution)
├─ Determine bootstrap candidate:
│   └─ If cluster is down: read seqno from grastate.dat across all pods
│       → highest seqno wins; ties broken by lowest pod number
│       → winner sets safe_to_bootstrap=1 and runs --wsrep-new-cluster
│       → others wait for winner, then join normally
├─ Configure sentinel.conf from /tmp/sentinel.conf (sed substitution)
├─ find_redis_primary():
│   └─ Query Redis Sentinel for current master
│       → if self is master: start redis-server as master
│       → if another pod is master: start as replica of that pod
├─ [pod 0 only] setup_wordpress() via wp-cli
├─ patch_host_redis_primary() — write redis.local to /etc/hosts
└─ supervisord
    ├─ php-fpm + nginx + delay
    ├─ galera-monitor (quorum watchdog)
    ├─ listen-redis (sentinel event subscriber → updates /etc/hosts on failover)
    └─ listen-redis-log (redis log monitor)
```

### Redis failover flow

```
Redis Sentinel detects master failure
    ↓
Elects new master
    ↓
Triggers client-reconfig-script: redis-failover-notify.sh
    ↓
listen-redis.sh receives +switch-master event via psubscribe
    ↓
Resolves new master hostname → IP address
    ↓
Updates /etc/hosts: "NEW_IP redis.local"
    ↓
Reconfigures local redis-server: SLAVEOF new-master-ip 6379
```

---

## 3. PHP 8.1 → PHP 8.4 Compatibility Review

### 3.1 PHP Core Changes

| Area | PHP 8.1 | PHP 8.4 | Action Required |
|------|---------|---------|----------------|
| JIT | Available, optional | Improved tracing mode | Enable `opcache.jit=tracing` + `opcache.jit_buffer_size=128M` |
| Implicit nullable params | Allowed (deprecated warning) | E_DEPRECATED in 8.4 | Plugins/themes must use `?Type` syntax |
| Dynamic properties | E_DEPRECATED in 8.2 | Removed in future | WordPress core compatible since 6.3 |
| `mysql_` functions | N/A (removed PHP 7.0) | N/A | No action |
| Readonly properties | PHP 8.1+ | Unchanged | N/A |
| Fibers | PHP 8.1+ | Unchanged | N/A |
| `array_is_list()` | PHP 8.1+ | Unchanged | N/A |
| API version | `20210902` | `20240924` | Extension paths updated |

**Official reference**: https://www.php.net/ChangeLog-8.php

### 3.2 OPcache Configuration Changes

| Directive | PHP 8.1 | PHP 8.4 | Notes |
|-----------|---------|---------|-------|
| `opcache.fast_shutdown` | Works (ignored) | REMOVED in PHP 7.2 | Remove from all ini files |
| `opcache.load_comments` | Works (ignored) | REMOVED in PHP 8.0 | Remove from all ini files |
| `opcache.save_comments` | Yes | Yes | Required by Doctrine annotations |
| `opcache.jit` | Optional | `tracing` recommended | Add to ini |
| `opcache.jit_buffer_size` | Optional | `128M` recommended | Add to ini |

**Official reference**: https://www.php.net/manual/en/opcache.configuration.php

### 3.3 PHP Extensions

| Extension | PHP 8.1 version | PHP 8.4 version | Notes |
|-----------|----------------|----------------|-------|
| xdebug | 3.1.5 | 3.4.0 | API unchanged since 3.x. Extension path: `no-debug-non-zts-20240924/` |
| apcu | (unversioned) | 5.1.24 | No breaking changes |
| phpredis | 5.3.7 | 6.0.x | **PHP 8.4 support added in 6.0.0**. Ref: https://github.com/phpredis/phpredis/releases/tag/6.0.0 |
| igbinary | latest | latest | No breaking changes for PHP 8.4 |
| imagick | 3.7.0 | 3.7.0 | Requires ImageMagick >= 6.5.3. PHP 8.4 compatible. |
| gd | built-in | built-in | Reconfigured with `--with-webp` in Layer 2 |
| intl | built-in | built-in | No changes |
| zip | built-in | built-in | No changes |
| mysqli | built-in | built-in | No changes |
| opcache | built-in | built-in | JIT improvements in 8.4 |
| sodium | built-in | built-in | WordPress 5.2+ uses sodium for password hashing |

### 3.4 Xdebug API Migration (2.x → 3.x)

PHP 8.4 requires xdebug 3.3+. xdebug 3.x completely replaced the configuration model:

| Old (xdebug 2.x) | New (xdebug 3.x) |
|---|---|
| `xdebug.remote_enable = on` | `xdebug.mode = debug` |
| `xdebug.remote_autostart = off` | `xdebug.start_with_request = no` |
| `xdebug.profiler_output_dir` | `xdebug.output_dir` |
| `xdebug.remote_host` | `xdebug.client_host` |
| `xdebug.remote_port = 9000` | `xdebug.client_port = 9003` |

Extension path change:
```
PHP 8.1: /usr/local/lib/php/extensions/no-debug-non-zts-20210902/xdebug.so
PHP 8.4: /usr/local/lib/php/extensions/no-debug-non-zts-20240924/xdebug.so
```

**Official reference**: https://xdebug.org/docs/upgrade_guide

### 3.5 MariaDB Changes (10.5 → 10.11)

Layer 1 switches from Alpine 3.18 (MariaDB 10.6.x via APK) to Alpine 3.21 (MariaDB 10.11.x via APK).

| Directive | MariaDB 10.5 | MariaDB 10.11 | Action |
|-----------|-------------|--------------|--------|
| `innodb_file_format = Barracuda` | Valid (no-op) | **REMOVED** (startup ERROR) | Delete from all cnf files |
| `innodb_large_prefix = 1` | Valid (no-op) | **REMOVED** (startup ERROR) | Delete from all cnf files |
| `innodb_flush_method=littlesync` | Valid | Deprecated (treated as `fsync`) | Change to `O_DIRECT` |
| `wsrep_on` | Valid | Valid | No change |
| `wsrep_provider` | `/usr/lib/galera/libgalera_smm.so` | `/usr/lib/libgalera_smm.so` | Path may differ by distro; verify at runtime |

**Official reference**: https://mariadb.com/kb/en/changes-improvements-in-mariadb-1011/

The `my-master-84.cnf` in this repo corrects all of the above. The original `my-master.cnf` (PHP 8.1 build) retains the old directives for compatibility with Alpine 3.18's MariaDB 10.6.

### 3.6 PHP 7.4 → 8.4 (Full Compatibility Path)

For teams migrating from the PHP 7.4 Dockerfile.template:

| Breaking Change | Introduced | Resolution |
|----------------|-----------|-----------|
| Named arguments | PHP 8.0 | Plugins using positional-only args work; plugins using named args in wrong order break |
| `match` expression | PHP 8.0 | New feature; no breakage |
| Nullsafe operator `?->` | PHP 8.0 | New feature |
| `str_contains/starts_with/ends_with` | PHP 8.0 | New feature; no breakage |
| Union types | PHP 8.0 | New feature |
| `array_key_first/last` | PHP 7.3+ | Available in both |
| JIT compilation | PHP 8.0 | Enable in opcache ini |
| Removed `each()` | PHP 8.0 | Some old plugins use it — must patch |
| Removed `create_function()` | PHP 8.0 | Old plugins may use it — must patch |
| Removed `$HTTP_*_VARS` | PHP 5.4 | N/A |
| Fibers | PHP 8.1 | New feature |
| Enums | PHP 8.1 | New feature |
| Readonly properties | PHP 8.1 | New feature |
| Intersection types | PHP 8.1 | New feature |
| Deprecated: implicit nullable | PHP 8.4 | `function foo(Type $x = null)` → `function foo(?Type $x = null)` |
| Deprecated: `E_STRICT` constant | PHP 8.4 | Remove from `error_reporting` calls |

**WordPress compatibility**: WordPress 6.7+ is fully PHP 8.4 compatible.
**Ref**: https://make.wordpress.org/core/handbook/best-practices/coding-standards/php-compatibility-and-wordpress-versions/

---

## 4. Image Registry

| PHP Version | Base | Final tag | Registry |
|-------------|------|----------|----------|
| PHP 8.1 | magicak-layer2:8.1.26-3.18v1.0 | `magicak-layer3-8.1-3.18v{VERSION}` | registry.margick.com (private) |
| PHP 7.4 | magicak-layer2:7.4.33-3.16v1.0 | `magicak-layer3-7.4-3.18v{VERSION}` | registry.margick.com (private) |
| PHP 8.4 | magicak-layer2:8.4.21-3.21v1.0 | `magicak-layer3-8.4-3.21v{VERSION}` | registry.margick.com (private) |

`VERSION` is read from the `VERSION` file in the root of this repo. Increment it for every production push.

---

## 5. Environment Variables

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `PLAN_TYPE` | No | `free` | `free` = standalone; `business` = Galera + Sentinel |
| `DATABASE_TYPE` | Yes | — | `local` starts MariaDB; unset = external DB |
| `DATABASE_HOST` | If external | — | External DB hostname |
| `DATABASE_NAME` | Yes | — | WordPress database name |
| `DATABASE_USERNAME` | Yes | — | DB user |
| `DATABASE_PASSWORD` | Yes | — | DB password |
| `DATABASE_PORT` | No | `3306` | DB port |
| `IS_SCALE` | No | `false` | `true` = dynamic Galera gcomm from replica count |
| `WORDPRESS_VERSION` | No | — | Pin WP version (wp-cli sets on first init) |
| `WP_PLUGIN_*` | No | — | Plugin version pins for wp-cli management |
| `WEBSITES_ENABLE_APP_SERVICE_STORAGE` | No | — | Azure App Service detection flag |
| `NGINX_LOG_DIR` | No | `/var/log/nginx` | Nginx log directory |
| `SUPERVISOR_LOG_DIR` | No | `/var/log/supervisor` | Supervisor log directory |
