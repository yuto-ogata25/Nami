locals {
  name_prefix = "${var.env}-${var.project}"

  # 2AZ構成（ALBのマルチAZ必須要件を満たすため）
  azs = ["${var.aws_region}a", "${var.aws_region}c"]
}