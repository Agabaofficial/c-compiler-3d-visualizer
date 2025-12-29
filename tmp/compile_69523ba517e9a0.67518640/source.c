#include <stdio.h>

int main() {
    int a = 10;
    int b = 20;
    int sum = a + b;

    printf("Sum is: %d\n, sum);   // ❌ Lexical error: unterminated string literal

    return 0;
}
