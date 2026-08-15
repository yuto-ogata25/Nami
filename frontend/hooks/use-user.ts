import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";

import { fetchCurrentUser, loginCustomer, logoutCustomer } from "@/lib/api/auth";
import { ApiError } from "@/lib/api/client";

export function useUser() {
  return useQuery({
    queryKey: ["user"],
    queryFn: fetchCurrentUser,
    retry: false,
    select: (response) => response.data,
    throwOnError: (error) => !(error instanceof ApiError && error.status === 401),
  });
}

export function useLoginCustomer() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: loginCustomer,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["user"] });
    },
  });
}

export function useLogoutCustomer() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: logoutCustomer,
    onSuccess: () => {
      // setQueryData(key, undefined) は更新なしと解釈され無視されるため、
      // removeQueries でキャッシュ自体を削除してログイン状態を確実にクリアする。
      queryClient.removeQueries({ queryKey: ["user"] });
    },
  });
}
