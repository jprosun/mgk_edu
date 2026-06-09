# mgk_edu_elementor — Workspace template Elementor (Margick)

Workspace **độc lập, đầy đủ** cho master template Elementor và việc nhân ra 200–300 biến thể đa ngành.
Đóng gói theo tư duy `magicak-wordpress`: image production Margick (Nginx + PHP-FPM + Redis + MariaDB + wp-cli), PHP-native shell + Locked Core + Editable Shell, generator seed lúc activate.

> Đọc trước: `../ONBOARDING.md` (tư duy 3 bề mặt) và `docs/TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR.md` (kỹ thuật).

## Cấu trúc

```
mgk_edu_elementor/
├── docker-compose.yml         image magicak-wordpress-wp-template:latest, port 8091
├── run.sh / restore-db.sh / rebuild.sh   vòng đời stack
├── new-template.sh / bundle.sh           đóng gói biến thể
├── my-local-override.cnf / php-fpm-local-override.conf / uploads.ini   config stack
├── wordpress_src/wp-config.php           wp-config mount read-only
├── db.sql                     DB seed (dump state thật)
├── data/                      webroot mount (full WP core + wp-content: themes/plugins/uploads)
├── docs/                      TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR + SRS + ARCHITECTURE (tham chiếu)
├── seed/                      (reserve — seed dùng chung nếu cần)
└── packages/
    └── mgk-edu-elementor/mgk-edu-elementor/   ★ SOURCE master theme (Hello Elementor child)
```

- **SOURCE** = `packages/mgk-edu-elementor/mgk-edu-elementor/` (sửa code ở đây).
- **RUNTIME** = `data/wp-content/themes/mgk-edu-elementor/` (container thấy cái này).
- Sửa SOURCE → `cp -a` sang RUNTIME → lint/verify → `diff -rq` sạch. (`rebuild.sh` sync ngược RUNTIME → SOURCE + dump DB.)

## Khởi động

```bash
./run.sh -d            # start container nền (port 8091)
./restore-db.sh        # nạp db.sql + đồng bộ siteurl về :8091 (lần đầu / khi DB trống)
```

- Site: http://localhost:8091   ·   Admin: http://localhost:8091/wp-admin
- DB: test / changeme   ·   Container: `mgk-edu-el`
- WP-CLI: `docker exec mgk-edu-el wp --allow-root --path=/var/www/html <cmd>`
- Generator layout: `docker exec mgk-edu-el wp --allow-root --path=/var/www/html mgk gen-layouts`

## Tạo biến thể mới

```bash
./new-template.sh mgk-fashion-001 fashion   # clone master → packages/<slug>/<slug>/
cp -a packages/mgk-fashion-001/mgk-fashion-001/. data/wp-content/themes/mgk-fashion-001/
docker exec mgk-edu-el wp --allow-root --path=/var/www/html theme activate mgk-fashion-001
./bundle.sh mgk-fashion-001                  # → packages/<slug>/<slug>-<version>.zip
```

## Lưu state / bàn giao

```bash
./rebuild.sh           # dump DB → db.sql + sync theme RUNTIME → SOURCE (packages)
```
