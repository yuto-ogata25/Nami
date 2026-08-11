# ============================================================
# Security Group（ALB用：インターネットからHTTPのみ受ける。
# egressはファイル末尾のaws_security_group_ruleで定義）
# ============================================================
resource "aws_security_group" "alb" {
  name        = "${local.name_prefix}-alb-sg"
  description = "ALB security group - allow HTTP from internet"
  vpc_id      = aws_vpc.main.id

  ingress {
    description = "HTTP from internet"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = { Name = "${local.name_prefix}-alb-sg" }
}

# ============================================================
# ALB本体
# ============================================================
resource "aws_lb" "main" {
  name               = "${local.name_prefix}-alb"
  internal           = false # インターネット向け
  load_balancer_type = "application"
  security_groups    = [aws_security_group.alb.id]
  subnets            = aws_subnet.public[*].id # Public subnetに配置

  tags = { Name = "${local.name_prefix}-alb" }
}

# ============================================================
# Target Group（frontend）
# ============================================================
resource "aws_lb_target_group" "frontend" {
  name        = "${local.name_prefix}-frontend-tg"
  port        = 3000
  protocol    = "HTTP"
  vpc_id      = aws_vpc.main.id
  target_type = "ip" # Fargateはip指定が必須

  health_check {
    path                = "/"
    healthy_threshold   = 2
    unhealthy_threshold = 3
    timeout             = 5
    interval            = 30
  }

  tags = { Name = "${local.name_prefix}-frontend-tg" }
}

# ============================================================
# Target Group（backend）
# ============================================================
resource "aws_lb_target_group" "backend" {
  name        = "${local.name_prefix}-backend-tg"
  port        = 8000
  protocol    = "HTTP"
  vpc_id      = aws_vpc.main.id
  target_type = "ip"

  health_check {
    path                = "/up" # Laravel標準のヘルスチェックルート
    healthy_threshold   = 2
    unhealthy_threshold = 3
    timeout             = 5
    interval            = 30
  }

  tags = { Name = "${local.name_prefix}-backend-tg" }
}

# ============================================================
# リスナー（HTTP:80）：デフォルトはfrontendへ
# ============================================================
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.main.arn
  port               = 80
  protocol           = "HTTP"

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.frontend.arn
  }
}

# ============================================================
# リスナールール：/api/* だけ backend へ
# ============================================================
resource "aws_lb_listener_rule" "api" {
  listener_arn = aws_lb_listener.http.arn
  priority     = 100

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.backend.arn
  }

  condition {
    path_pattern {
      values = ["/api/*"]
    }
  }
}

# ============================================================
# ALB egress（ECS Fargateの待ち受けポートにのみ許可）
# SG内に書くとECS SGとの循環参照になるため、別リソースに切り出している
# ============================================================
resource "aws_security_group_rule" "alb_egress_frontend" {
  type                     = "egress"
  description              = "to frontend container"
  from_port                = 3000
  to_port                  = 3000
  protocol                 = "tcp"
  security_group_id        = aws_security_group.alb.id
  source_security_group_id = aws_security_group.ecs.id
}

resource "aws_security_group_rule" "alb_egress_backend" {
  type                     = "egress"
  description              = "to backend container"
  from_port                = 8000
  to_port                  = 8000
  protocol                 = "tcp"
  security_group_id        = aws_security_group.alb.id
  source_security_group_id = aws_security_group.ecs.id
}