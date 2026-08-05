# ============================================================
# ECS Task Execution Role
# （ECRからのイメージpull、CloudWatch Logsへの書き込み権限）
# ============================================================
resource "aws_iam_role" "ecs_task_execution" {
  name = "${local.name_prefix}-ecs-task-execution-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Action = "sts:AssumeRole"
      Effect = "Allow"
      Principal = {
        Service = "ecs-tasks.amazonaws.com"
      }
    }]
  })

  tags = { Name = "${local.name_prefix}-ecs-task-execution-role" }
}

# AWS管理ポリシーをアタッチ（ECR pull・CloudWatch Logs書き込みが含まれる）
resource "aws_iam_role_policy_attachment" "ecs_task_execution" {
  role       = aws_iam_role.ecs_task_execution.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"
}

# ============================================================
# ECS Task Role
# （アプリ実行中にAWSサービスを呼ぶ権限。今は最小限、空に近い）
# ============================================================
resource "aws_iam_role" "ecs_task" {
  name = "${local.name_prefix}-ecs-task-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Action = "sts:AssumeRole"
      Effect = "Allow"
      Principal = {
        Service = "ecs-tasks.amazonaws.com"
      }
    }]
  })

  tags = { Name = "${local.name_prefix}-ecs-task-role" }
}