# ============================================================
# ECS Cluster
# ============================================================
resource "aws_ecs_cluster" "main" {
  name = "${local.name_prefix}-cluster"

  tags = { Name = "${local.name_prefix}-cluster" }
}

# ============================================================
# Security Group（ECS Fargate用：ALBから、必要なポートのみ許可）
#
# 送信元をALBのSGに限定したうえで、ポートもコンテナが待ち受ける
# 3000 / 8000 だけに絞る。全ポート開放だと「ALBが侵害されたとき
# タスクの全ポートに到達できる」状態になり、多層防御の思想と合わないため。
# ============================================================
resource "aws_security_group" "ecs" {
  name        = "${local.name_prefix}-ecs-sg"
  description = "ECS Fargate security group - allow traffic from ALB only"
  vpc_id      = aws_vpc.main.id

  ingress {
    description     = "frontend from ALB"
    from_port       = 3000
    to_port         = 3000
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id] # ALBからのみ、直接インターネットからは不可
  }

  ingress {
    description     = "backend from ALB"
    from_port       = 8000
    to_port         = 8000
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }

  egress {
    description = "to internet (via NAT)"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"] # 外部決済APIを叩く前提のため宛先は絞らない
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