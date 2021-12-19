CREATE TABLE `services` (
                             `service_num` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '서비스고유ID',
                             `service_name` VARCHAR(100) NOT NULL COMMENT '서비스명' COLLATE 'utf8mb4_unicode_ci',
                             `api_key` VARCHAR(255) NULL DEFAULT NULL COMMENT '인증키' COLLATE 'utf8mb4_unicode_ci',
                             `created_at` TIMESTAMP NULL DEFAULT NULL COMMENT '생성일',
                             `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT '변경일',
                             PRIMARY KEY (`service_num`)
)
COMMENT='각 서비스별로 인증키 저장 테이블'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE `users` (
                         `user_num` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT '사용자 고유번호',
                         `name` VARCHAR(20) NOT NULL COMMENT '이름' COLLATE 'utf8_unicode_ci',
                         `nickname` VARCHAR(30) NOT NULL COMMENT '별명' COLLATE 'utf8_unicode_ci',
                         `password` VARCHAR(100) NOT NULL COMMENT '비밀번호' COLLATE 'utf8_unicode_ci',
                         `phone` VARCHAR(20) NOT NULL COMMENT '전화번호' COLLATE 'utf8_unicode_ci',
                         `email` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                         `gender` CHAR(1) NULL DEFAULT NULL COMMENT '성별 M:남성,F:여성,O:기타' COLLATE 'utf8_unicode_ci',
                         `token` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                         `token_created` DATETIME NULL DEFAULT NULL,
                         `created_at` TIMESTAMP NULL DEFAULT NULL,
                         `updated_at` TIMESTAMP NULL DEFAULT NULL,
                         PRIMARY KEY (`user_num`),
                         UNIQUE INDEX `email` (`email`),
                         INDEX `user_num` (`user_num`)
)
    COMMENT='회원 정보 테이블'
    COLLATE='utf8_unicode_ci'
    ENGINE=InnoDB
;

CREATE TABLE `orders` (
                          `order_num` VARCHAR(12) NOT NULL COMMENT '주문 고유번호' COLLATE 'utf8_unicode_ci',
                          `user_num` BIGINT(20) NOT NULL COMMENT '사용자 고유번호',
                          `product_name` VARCHAR(100) NOT NULL COMMENT '제품명' COLLATE 'utf8_unicode_ci',
                          `payment_created` DATETIME NULL DEFAULT NULL,
                          `created_at` TIMESTAMP NULL DEFAULT NULL,
                          `updated_at` TIMESTAMP NULL DEFAULT NULL,
                          PRIMARY KEY (`order_num`),
                          UNIQUE INDEX `order` (`order_num`),
                          INDEX `order_num` (`order_num`),
                          INDEX `FK1_user` (`user_num`),
                          CONSTRAINT `FK1_user` FOREIGN KEY (`user_num`) REFERENCES `users` (`user_num`)
)
    COMMENT='주문 정보 테이블'
    COLLATE='utf8_unicode_ci'
    ENGINE=InnoDB
;

