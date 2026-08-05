# ============================================================
# VPC
# ============================================================
resource "aws_vpc" "main" {
  cidr_block           = var.vpc_cidr
  enable_dns_support   = true # Interfaceエンドポイント（将来利用時）のprivate DNS解決に必須
  enable_dns_hostnames = true # ALB・ECS Fargateのホスト名解決に必要

  tags = { Name = "${local.name_prefix}-vpc" }
}

# ============================================================
# Public Subnet（ALB・NAT Gateway用）
# ============================================================
resource "aws_subnet" "public" {
  count                   = length(local.azs)
  vpc_id                  = aws_vpc.main.id
  availability_zone       = local.azs[count.index]
  cidr_block              = cidrsubnet(var.vpc_cidr, 8, count.index + 1) # 10.0.1.0/24, 10.0.2.0/24
  map_public_ip_on_launch = true                                        # ALB・NAT Gatewayはpublic IPが必要

  tags = { Name = "${local.name_prefix}-public-${local.azs[count.index]}" }
}

# ============================================================
# Private Subnet（ECS Fargate用）
# ============================================================
resource "aws_subnet" "private" {
  count                   = length(local.azs)
  vpc_id                  = aws_vpc.main.id
  availability_zone       = local.azs[count.index]
  cidr_block              = cidrsubnet(var.vpc_cidr, 8, count.index + 11) # 10.0.11.0/24, 10.0.12.0/24
  map_public_ip_on_launch = false                                        # Fargateタスクを外部に直接晒さない

  tags = { Name = "${local.name_prefix}-private-${local.azs[count.index]}" }
}

# ============================================================
# Internet Gateway（Public Subnet用の出入口）
# ============================================================
resource "aws_internet_gateway" "main" {
  vpc_id = aws_vpc.main.id

  tags = { Name = "${local.name_prefix}-igw" }
}

# ============================================================
# NAT Gateway（1個構成：開発フェーズにつきコスト優先。
# 外部の利用者がつき次第、2個構成に切り替え予定）
# ============================================================
resource "aws_eip" "nat" {
  domain = "vpc"

  tags = { Name = "${local.name_prefix}-nat-eip" }
}

resource "aws_nat_gateway" "main" {
  allocation_id = aws_eip.nat.id
  subnet_id     = aws_subnet.public[0].id # AZ-aのpublic subnetに配置

  tags = { Name = "${local.name_prefix}-nat" }

  depends_on = [aws_internet_gateway.main]
}

# ============================================================
# Public Route Table（IGW経由で0.0.0.0/0）
# ============================================================
resource "aws_route_table" "public" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.main.id
  }

  tags = { Name = "${local.name_prefix}-public-rt" }
}

resource "aws_route_table_association" "public" {
  count          = length(aws_subnet.public)
  subnet_id      = aws_subnet.public[count.index].id
  route_table_id = aws_route_table.public.id
}

# ============================================================
# Private Route Table（NAT Gateway経由で0.0.0.0/0）
# ============================================================
resource "aws_route_table" "private" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.main.id
  }

  tags = { Name = "${local.name_prefix}-private-rt" }
}

resource "aws_route_table_association" "private" {
  count          = length(aws_subnet.private)
  subnet_id      = aws_subnet.private[count.index].id
  route_table_id = aws_route_table.private.id
}