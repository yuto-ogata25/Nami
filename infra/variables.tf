variable "aws_region" {
  description = "AWSリージョン"
  type        = string
  default     = "ap-northeast-1"
}

variable "aws_profile" {
  description = "AWS CLIプロファイル名"
  type        = string
  default     = "default"
}

variable "env" {
  description = "環境名（dev/prod）"
  type        = string
}

variable "project" {
  description = "プロジェクト名（リソース名のprefixに使う）"
  type        = string
  default     = "nami"
}

variable "vpc_cidr" {
  description = "VPCのCIDR"
  type        = string
  default     = "10.0.0.0/16"
}