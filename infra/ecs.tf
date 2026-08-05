# ============================================================
# ECS Cluster
# ============================================================
resource "aws_ecs_cluster" "main" {
  name = "${local.name_prefix}-cluster"

  tags = { Name = "${local.name_prefix}-cluster" }
}

# ============================================================
# Security Group（ECS Fargate用：ALBからの通信のみ許可）
# ============================================================
resource "aws_security_group" "ecs" {
  name        = "${local.name_prefix}-ecs-sg"
  description = "ECS Fargate security group - allow traffic from ALB only"
  vpc_id      = aws_vpc.main.id

  ingress {
    description     = "from ALB"
    from_port       = 0
    to_port         = 65535
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id] # ALBからのみ、直接インターネットからは不可
  }

  egress {
    description = "to internet (via NAT)"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = { Name = "${local.name_prefix}-ecs-sg" }
}

# ============================================================
# CloudWatch Log Group（frontend / backend）
# ============================================================
resource "aws_cloudwatch_log_group" "frontend" {
  name              = "/ecs/${local.name_prefix}-frontend"
  retention_in_days = 7 # 検証フェーズなので短め。本番稼働時に見直し

  tags = { Name = "${local.name_prefix}-frontend-logs" }
}

resource "aws_cloudwatch_log_group" "backend" {
  name              = "/ecs/${local.name_prefix}-backend"
  retention_in_days = 7

  tags = { Name = "${local.name_prefix}-backend-logs" }
}